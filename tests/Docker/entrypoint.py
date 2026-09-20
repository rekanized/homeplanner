#!/usr/bin/env python3
"""Exercise container initialization in disposable directories with real Laravel."""
import base64
import json
import os
from pathlib import Path
import secrets
import shutil
import sqlite3
import subprocess
import tempfile

ROOT = Path(__file__).resolve().parents[2]
PHP = shutil.which(os.environ.get("TEST_PHP", "php"))
assert PHP, "Install PHP or set TEST_PHP to its executable"
assert (ROOT / "vendor/autoload.php").exists(), "Run composer install first"

with tempfile.TemporaryDirectory(prefix="homeplanner-entrypoint-") as directory:
    temp = Path(directory)
    app = temp / "app"
    app.mkdir()
    for name in ("app", "bootstrap", "config", "database", "docker", "lang", "public", "resources", "routes", "vendor"):
        shutil.copytree(ROOT / name, app / name, ignore=shutil.ignore_patterns(
            ".env", ".env.*", "*.sqlite*", "*.db", "*.sql", "EconomyBackupSeeder.php", "storage"
        ))
    for path in (app / "bootstrap/cache").glob("*.php"):
        path.unlink()
    for name in ("artisan", "composer.json", "composer.lock"):
        shutil.copy2(ROOT / name, app / name)
    bin_dir = temp / "bin"
    bin_dir.mkdir()
    (bin_dir / "php").symlink_to(PHP)
    # Test only initialization: do not start a server on a host network port.
    (bin_dir / "php-fpm").write_text('#!/bin/sh\necho FPM_STARTED\n')
    (bin_dir / "php-fpm").chmod(0o755)
    env = {
        "PATH": f"{bin_dir}:{os.environ['PATH']}",
        "HOME": str(temp),
        "APP_ENV": "production",
        "APP_DEBUG": "false",
        "APP_URL": "http://first.example.test",
        "LOG_CHANNEL": "stderr",
        "DB_CONNECTION": "sqlite",
        "DB_DATABASE": str(temp / "data/database.sqlite"),
        "CACHE_STORE": "database",
        "SESSION_DRIVER": "database",
    }

    def run(*args, override=None, success=True):
        result = subprocess.run(args, cwd=app, env=env | (override or {}),
                                text=True, capture_output=True, timeout=120)
        if success:
            assert result.returncode == 0, result.stdout + result.stderr
        else:
            assert result.returncode != 0, "Startup should have failed"
        return result.stdout + result.stderr

    def start(override=None, success=True):
        return run("sh", "docker/entrypoint.sh", "php-fpm", override=override, success=success)

    assert "requires DB_CONNECTION" in start({"DB_CONNECTION": "mysql"}, success=False)
    assert "requires DB_CONNECTION" in start({"DB_URL": "sqlite:///unexpected.db"}, success=False)
    assert "absolute path" in start({"DB_DATABASE": "relative.sqlite"}, success=False)
    assert not (temp / "data").exists()
    assert "FPM_STARTED" in start()
    assert not (app / ".env").exists()
    key_file = temp / "data/app.key"
    key = key_file.read_text()
    assert key.startswith("base64:") and len(base64.b64decode(key[7:])) == 32
    assert key_file.stat().st_mode & 0o777 == 0o600
    with sqlite3.connect(env["DB_DATABASE"]) as db:
        assert db.execute("select count(*) from migrations").fetchone()[0] > 0
        db.execute("create table docker_sentinel (value text)")
        db.execute("insert into docker_sentinel values ('preserved')")

    # A restart must retain data and key while refreshing changed environment.
    env["APP_URL"] = "http://second.example.test"
    assert "FPM_STARTED" in start()
    config = json.loads(run("php", "-r", "echo json_encode(require 'bootstrap/cache/config.php');"))
    assert config["app"]["key"] == key
    assert config["app"]["url"] == env["APP_URL"]
    with sqlite3.connect(env["DB_DATABASE"]) as db:
        assert db.execute("select value from docker_sentinel").fetchone()[0] == "preserved"

    # Simulate a recreated container: discard container-local caches, retain data/key.
    for path in (app / "bootstrap/cache").glob("*.php"):
        path.unlink()
    assert "FPM_STARTED" in start()
    config = json.loads(run("php", "-r", "echo json_encode(require 'bootstrap/cache/config.php');"))
    assert config["app"]["key"] == key
    with sqlite3.connect(env["DB_DATABASE"]) as db:
        assert db.execute("select value from docker_sentinel").fetchone()[0] == "preserved"

    # An explicit key is optional and does not replace the generated default.
    supplied_key = "base64:" + base64.b64encode(secrets.token_bytes(32)).decode()
    assert "FPM_STARTED" in start({"APP_KEY": supplied_key})
    config = json.loads(run("php", "-r", "echo json_encode(require 'bootstrap/cache/config.php');"))
    assert config["app"]["key"] == supplied_key
    assert key_file.read_text() == key

    output = run("sh", "docker/entrypoint.sh", "php", "artisan", "schedule:list")
    assert "economy:capture-snapshot" in output
    assert "kids:generate-recurring" in output
    assert "Running migrations" not in output
    run("sh", "docker/entrypoint.sh", "php", "artisan", "queue:work", "--once")

    if os.environ.get("TEST_HTTP") == "1":
        from nginx_smoke import check_http
        check_http(ROOT, app, temp, env, run)

    # An unusable path must fail before the server starts.
    blocker = temp / "not-a-directory"
    blocker.write_text("block")
    output = start({"DB_DATABASE": str(blocker / "database.sqlite")}, success=False)
    assert "FPM_STARTED" not in output

    # A failed migration must never leave an apparently running app server.
    migration = app / "database/migrations/2099_01_01_000000_docker_failure.php"
    migration.write_text(r"""<?php
return new class extends \Illuminate\Database\Migrations\Migration {
    public function up(): void { throw new \RuntimeException('intentional migration failure'); }
};
""")
    output = start(success=False)
    assert "intentional migration failure" in output
    assert "FPM_STARTED" not in output

print("PASS: automatic key generation, first boot, restart, recreation, key override, workers, and failure propagation")
