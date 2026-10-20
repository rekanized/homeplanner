#!/usr/bin/env python3
"""Audit a populated, isolated Homeplanner instance with Python Playwright.

Set VISUAL_AUDIT_URL, VISUAL_AUDIT_EMAIL, VISUAL_AUDIT_PASSWORD, and optionally
VISUAL_AUDIT_OUTPUT and CHROMIUM_EXECUTABLE. Use synthetic data locally.
"""

import asyncio
import json
import os
from pathlib import Path
from urllib.parse import urlparse

from playwright.async_api import async_playwright

BASE_URL = os.environ.get("VISUAL_AUDIT_URL", "http://127.0.0.1:8896").rstrip("/")
OUTPUT = Path(os.environ.get("VISUAL_AUDIT_OUTPUT", "/tmp/homeplanner-visual-review"))
ROUTES = [
    "/", "/economy", "/economy/savings", "/economy/history",
    "/economy/savings-history", "/shopping", "/todo", "/kids",
    "/admin/users", "/admin/settings", "/admin/logs", "/admin/versions",
]
WIDTHS = [320, 360, 390, 430, 640, 768, 820, 1024, 1280, 1440, 1920]

LAYOUT_CHECK = """() => {
    const visible = element => {
        const box = element.getBoundingClientRect();
        const style = getComputedStyle(element);
        return box.width > 0 && box.height > 0
            && style.visibility !== 'hidden' && style.display !== 'none';
    };
    const root = document.querySelector('.modal-content') || document.querySelector('main');
    return {
        documentOverflow: document.documentElement.scrollWidth > innerWidth + 1,
        overflow: [...root.querySelectorAll('*')].filter(visible).filter(element => {
            const box = element.getBoundingClientRect();
            return (box.left < -2 || box.right > innerWidth + 2) && !element.closest('svg');
        }).slice(0, 25).map(element => ({
            tag: element.tagName,
            class: typeof element.className === 'string' ? element.className : 'svg',
            label: (element.getAttribute('aria-label') || element.textContent || '').trim().slice(0, 70),
        })),
        unnamedButtons: [...document.querySelectorAll('button')].filter(visible)
            .filter(element => !element.textContent.trim()
                && !element.getAttribute('aria-label') && !element.title)
            .map(element => element.outerHTML.slice(0, 250)),
    };
}"""


async def screenshots(page, route, width, dark, editing):
    stem = route.strip("/").replace("/", "-") or "home"
    stem += ("-edit" if editing else "") + ("-dark" if dark else "") + f"-{width}"
    directory = OUTPUT / "screenshots"
    await page.screenshot(path=str(directory / f"{stem}.png"), full_page=True)
    await page.evaluate("window.scrollTo(0, 0)")
    await page.screenshot(path=str(directory / f"top-{stem}.png"))
    await page.evaluate("window.scrollTo(0, document.body.scrollHeight)")
    await page.screenshot(path=str(directory / f"bottom-{stem}.png"))
    await page.evaluate("window.scrollTo(0, 0)")


async def main():
    if urlparse(BASE_URL).hostname not in {"127.0.0.1", "localhost", "::1"}:
        raise SystemExit("Use an isolated local instance with synthetic data.")
    (OUTPUT / "screenshots").mkdir(parents=True, exist_ok=True)
    report = {"results": [], "errors": [], "failed": []}

    async with async_playwright() as playwright:
        browser = await playwright.chromium.launch(
            headless=True,
            executable_path=os.environ.get("CHROMIUM_EXECUTABLE")
            or playwright.chromium.executable_path,
            args=["--no-sandbox"],
        )
        page = await browser.new_page(viewport={"width": 390, "height": 900}, has_touch=True)
        page.on("pageerror", lambda error: report["errors"].append({
            "url": page.url, "error": str(error),
        }))
        page.on("console", lambda message: report["errors"].append({
            "url": page.url, "console": message.text,
        }) if message.type == "error" else None)
        page.on("response", lambda response: report["failed"].append({
            "url": response.url, "status": response.status,
        }) if response.status >= 400 and urlparse(response.url).netloc
            == urlparse(BASE_URL).netloc else None)
        page.on("requestfailed", lambda request: report["failed"].append({
            "url": request.url, "failure": request.failure,
        }) if urlparse(request.url).netloc == urlparse(BASE_URL).netloc
            and request.failure != "net::ERR_ABORTED" else None)
        try:
            await page.goto(BASE_URL + "/login", wait_until="domcontentloaded")
            await page.locator("#login-email").fill(
                os.environ.get("VISUAL_AUDIT_EMAIL", "audit@example.test")
            )
            await page.locator("#login-password").fill(
                os.environ.get("VISUAL_AUDIT_PASSWORD", "audit-password-2026")
            )
            await page.locator('form button[type="submit"]').click()
            await page.wait_for_url(BASE_URL + "/")

            for route in ROUTES:
                response = await page.goto(BASE_URL + route, wait_until="domcontentloaded")
                assert response.status == 200, f"{route}: HTTP {response.status}"
                assert page.url.rstrip("/") == (BASE_URL + route).rstrip("/"), page.url
                await page.locator("#main-content").wait_for()
                for editing in [False, True]:
                    if editing:
                        toggle = page.locator('button[wire\\:click="toggleEditMode"]')
                        if not await toggle.count():
                            continue
                        await toggle.click()
                        await page.locator(".eco-edit-table").first.wait_for()
                    for dark in [False, True]:
                        await page.evaluate(
                            "dark => document.documentElement.classList.toggle('dark', dark)", dark
                        )
                        for width in WIDTHS:
                            await page.set_viewport_size({"width": width, "height": 900})
                            await page.wait_for_timeout(200)
                            report["results"].append({
                                "route": route, "width": width, "dark": dark,
                                "editing": editing, "status": response.status,
                                **await page.evaluate(LAYOUT_CHECK),
                            })
                            if width in [320, 768, 1440]:
                                await screenshots(page, route, width, dark, editing)
                print(f"{route}: checked", flush=True)
        finally:
            await browser.close()
            (OUTPUT / "matrix.json").write_text(json.dumps(report, indent=2) + "\n")

    issues = [result for result in report["results"] if result["documentOverflow"]
              or result["overflow"] or result["unnamedButtons"]]
    print(json.dumps({
        "checks": len(report["results"]), "issues": issues,
        "errors": report["errors"], "failed": report["failed"],
    }, indent=2))
    assert not issues and not report["errors"] and not report["failed"], "See matrix.json."


if __name__ == "__main__":
    asyncio.run(main())
