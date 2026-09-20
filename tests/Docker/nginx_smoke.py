"""Optional HTTP checks using host Nginx/PHP-FPM, with isolated ports and files."""
import json
import os
import shutil
import socket
import subprocess
import time
import urllib.error
import urllib.request


def check_http(root, app, temp, env, run):
    nginx = shutil.which("nginx")
    fpm = shutil.which(os.environ.get("TEST_FPM", "php-fpm"))
    assert nginx and fpm, "Install Nginx/PHP-FPM or set TEST_FPM"

    def port():
        with socket.socket() as sock:
            sock.bind(("127.0.0.1", 0))
            return sock.getsockname()[1]

    http_port, fpm_port = port(), port()
    fpm_conf = temp / "fpm.conf"
    fpm_conf.write_text(f"""[global]
error_log = {temp}/fpm.log
[www]
listen = 127.0.0.1:{fpm_port}
pm = static
pm.max_children = 2
clear_env = no
catch_workers_output = yes
""")
    # Translate only container addresses/paths; keep all routing rules unchanged.
    site = (root / "docker/nginx.conf").read_text()
    site = site.replace("app:9000 resolve", f"127.0.0.1:{fpm_port}")
    site = site.replace("listen 8080;", f"listen 127.0.0.1:{http_port};")
    site = site.replace("listen [::]:8080;", "")
    site = site.replace("/app/public", str(app / "public"))
    site = site.replace("include fastcgi_params;", "include /etc/nginx/fastcgi_params;")
    (temp / "site.conf").write_text(site)
    main = (root / "docker/nginx-main.conf").read_text()
    main = main.replace("/tmp/", str(temp) + "/")
    main = main.replace("/etc/nginx/conf.d/*.conf", str(temp / "site.conf"))
    (temp / "nginx.conf").write_text(main)
    (app / "public/storage").symlink_to(app / "storage/app/public")
    fixtures = {
        "test.txt": "public-file",
        "injected.php": '<?php echo "must-not-execute";',
        ".hidden.js": "must-not-serve",
        "chores/proofs/test.jpg": "private-proof",
    }
    for name, content in fixtures.items():
        path = app / "storage/app/public" / name
        path.parent.mkdir(parents=True, exist_ok=True)
        path.write_text(content)
    nginx_args = [nginx, "-e", str(temp / "nginx.log"), "-c", str(temp / "nginx.conf")]
    run(*nginx_args, "-t")
    processes = []
    with open(temp / "fpm-output.log", "w") as fpm_log, open(temp / "nginx-output.log", "w") as nginx_log:
        try:
            processes.append(subprocess.Popen([fpm, "-F", "-y", str(fpm_conf)],
                             cwd=app, env=env, stdout=fpm_log, stderr=fpm_log))
            processes.append(subprocess.Popen(nginx_args + ["-g", "daemon off;"],
                             cwd=app, env=env, stdout=nginx_log, stderr=nginx_log))

            def request(path):
                try:
                    response = urllib.request.urlopen(f"http://127.0.0.1:{http_port}" + path, timeout=5)
                except urllib.error.HTTPError as exc:
                    response = exc
                with response:
                    return response.status, response.read()

            for attempt in range(50):
                try:
                    if request("/up")[0] == 200:
                        break
                except urllib.error.URLError:
                    pass
                time.sleep(0.1)
            else:
                raise AssertionError((temp / "fpm-output.log").read_text()
                                     + (temp / "nginx-output.log").read_text())
            assert request("/css/style.css")[0] == 200
            assert request("/storage/test.txt") == (200, b"public-file")
            assert request("/storage/injected.php")[0] == 404
            assert request("/storage/.hidden.js")[0] == 403
            assert request("/storage/chores/proofs/test.jpg")[0] == 404
            assert request("/.env")[0] == 403
            routes = json.loads(run("php", "artisan", "route:list", "--json"))
            scripts = [r["uri"] for r in routes if "livewire" in r["uri"] and r["uri"].endswith(".js") and "{" not in r["uri"]]
            assert scripts, "No Livewire scripts found"
            for script in scripts:
                assert request("/" + script)[0] == 200, script
            print("PASS: HTTP health, CSS, Livewire scripts, public storage, blocked PHP/dotfiles/private proofs")
        finally:
            for process in processes:
                process.terminate()
            for process in processes:
                process.wait(timeout=10)
