#!/usr/bin/env python3
"""Check that the container manages all three processes and forwards stop signals."""
import os
from pathlib import Path
import signal
import subprocess
import sys
import tempfile
import time

ROOT = Path(__file__).resolve().parents[2]

with tempfile.TemporaryDirectory(prefix="homeplanner-processes-") as directory:
    temp = Path(directory)
    child = f"#!{sys.executable}\n" + '''import os, pathlib, signal, sys, time
name = 'fpm' if pathlib.Path(sys.argv[0]).name == 'php-fpm' else ('scheduler' if 'schedule:work' in sys.argv else 'worker')
pathlib.Path(name + '.pid').write_text(str(os.getpid()))
def stop(number, frame):
    pathlib.Path(name + '.signal').write_text(str(number))
    sys.exit(0)
signal.signal(signal.SIGTERM, stop)
signal.signal(signal.SIGQUIT, stop)
while not all(pathlib.Path(name + '.pid').exists() for name in ('fpm', 'scheduler', 'worker')):
    time.sleep(0.01)
if os.environ.get('FAIL_PROCESS') == name:
    time.sleep(0.1)
    sys.exit(7)
while True:
    time.sleep(0.05)
'''
    for name in ("php-fpm", "php"):
        path = temp / name
        path.write_text(child)
        path.chmod(0o755)

    for failure in ("", "fpm", "scheduler", "worker"):
        for path in temp.glob("*.pid"):
            path.unlink()
        for path in temp.glob("*.signal"):
            path.unlink()
        env = os.environ | {"PATH": f"{temp}:{os.environ['PATH']}", "FAIL_PROCESS": failure}
        process = subprocess.Popen(["bash", str(ROOT / "docker/start.sh")], cwd=temp, env=env,
                                   stdout=subprocess.PIPE, stderr=subprocess.PIPE, start_new_session=True)
        try:
            deadline = time.monotonic() + 5
            while not all((temp / f"{name}.pid").exists() for name in ("fpm", "scheduler", "worker")):
                assert time.monotonic() < deadline, "Not all processes started"
                time.sleep(0.01)
            if not failure:
                # Give each child time to install its signal handlers.
                time.sleep(0.1)
                process.send_signal(signal.SIGTERM)
            stdout, stderr = process.communicate(timeout=5)
            assert process.returncode == (7 if failure else 0), stderr.decode()
            for name in ("fpm", "scheduler", "worker"):
                if name == failure:
                    continue
                expected = signal.SIGQUIT if name == "fpm" else signal.SIGTERM
                assert int((temp / f"{name}.signal").read_text()) == expected
        finally:
            try:
                os.killpg(process.pid, signal.SIGKILL)
            except ProcessLookupError:
                pass
            process.wait()

print("PASS: three-process startup, graceful shutdown, and failure of each child")
