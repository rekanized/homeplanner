// Run against an isolated, populated local test instance. See docs/mobile-overhaul.md.
const assert = require('node:assert/strict');
const fs = require('node:fs/promises');
const path = require('node:path');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');

const baseURL = process.env.MOBILE_AUDIT_URL || 'http://127.0.0.1:8876';
const output = process.env.MOBILE_AUDIT_OUTPUT || '/tmp/homeplanner-mobile-review';
const routes = [
    '/', '/shopping', '/todo', '/economy', '/economy/savings', '/economy/history',
    '/economy/savings-history', '/kids', '/admin/users', '/admin/settings', '/admin/logs', '/admin/versions',
];
const widths = [320, 360, 390, 430, 640, 700, 768, 820, 1024, 1280, 1440];

async function run() {
    assert(['localhost', '127.0.0.1', '[::1]'].includes(new URL(baseURL).hostname), 'Use an isolated local test server.');
    await fs.mkdir(output, { recursive: true });
    const browser = await chromium.launch({
        headless: true,
        executablePath: process.env.CHROMIUM_EXECUTABLE || undefined,
        args: ['--no-sandbox'],
    });
    const page = await browser.newPage({ viewport: { width: 390, height: 844 }, hasTouch: true });
    const errors = [];
    const results = [];
    page.on('pageerror', error => errors.push(error.message));

    try {
        await page.goto(`${baseURL}/login`);
        await page.locator('#login-email').fill(process.env.MOBILE_AUDIT_EMAIL || 'audit@example.test');
        await page.locator('#login-password').fill(process.env.MOBILE_AUDIT_PASSWORD || 'audit-password-2026');
        await page.locator('form button[type="submit"]').click();
        await page.waitForURL(`${baseURL}/`);

        for (const route of routes) {
            const response = await page.goto(baseURL + route);
            assert.equal(response.status(), 200, route);
            await page.locator('#main-content').waitFor();

            for (const editing of [false, true]) {
                if (editing) {
                    const toggle = page.locator('button[wire\\:click="toggleEditMode"]');
                    if (!await toggle.count()) continue;
                    await toggle.click();
                    await page.locator('.eco-edit-table').first().waitFor();
                }

                for (const width of widths) {
                    await page.setViewportSize({ width, height: width === 820 ? 390 : 844 });
                    // Allow the layout, chart ResizeObserver, and entry animation to settle.
                    await page.waitForTimeout(300);
                    const overflow = await page.evaluate(() => [...document.querySelectorAll('main *')]
                        .filter(element => {
                            const box = element.getBoundingClientRect();
                            return box.width && box.height && getComputedStyle(element).visibility !== 'hidden'
                                && (box.left < -2 || box.right > innerWidth + 2);
                        })
                        .map(element => ({
                            tag: element.tagName,
                            class: typeof element.className === 'string' ? element.className : element.className.baseVal,
                            label: (element.getAttribute('aria-label') || element.textContent.trim()).slice(0, 80),
                        })));
                    results.push({ route, width, editing, overflow });
                    if (width === 390) {
                        const filename = `${route.replaceAll('/', '-') || 'home'}${editing ? '-edit' : ''}.png`;
                        await page.screenshot({ path: path.join(output, filename), fullPage: true });
                    }
                }
            }
        }

        for (const viewport of [{ width: 320, height: 568 }, { width: 390, height: 844 }, { width: 820, height: 390 }]) {
            await page.setViewportSize(viewport);
            for (const trigger of await page.locator('.mobile-tabbar button').all()) {
                await trigger.click();
                const sheet = page.locator('.mobile-nav-sheet');
                await sheet.waitFor({ state: 'visible' });
                await page.waitForTimeout(300);
                assert(await page.locator('#main-content').evaluate(element => element.inert));
                const bounds = await sheet.boundingBox();
                assert(bounds.y >= 0 && bounds.y + bounds.height <= viewport.height, 'Navigation must fit the viewport.');
                await page.keyboard.press('Tab');
                assert(await sheet.evaluate(element => element.contains(document.activeElement)));
                await page.keyboard.press('Shift+Tab');
                assert(await sheet.evaluate(element => element.contains(document.activeElement)));
                await page.keyboard.press('Escape');
                await sheet.waitFor({ state: 'hidden' });
                await page.waitForTimeout(100);
                assert(!await page.locator('#main-content').evaluate(element => element.inert));
                assert(await trigger.evaluate(element => element === document.activeElement));
            }
        }

        await page.setViewportSize({ width: 390, height: 844 });
        await page.goto(`${baseURL}/economy/history`);
        if (await page.locator('.history-chart-point').count()) {
            // Reproduce tapping a chart after scrolling and selecting a snapshot.
            const snapshot = page.locator('.history-select-button').last();
            await snapshot.focus();
            await page.keyboard.press('Enter');
            await page.waitForTimeout(350);
            await page.locator('.history-chart-point').first().tap();
            await page.waitForTimeout(350);
            assert(await page.locator('.history-chart-tooltip').isVisible(), 'Touch tooltip must remain visible.');
        }

        await fs.writeFile(path.join(output, 'mobile-audit.json'), JSON.stringify({ results, errors }, null, 2));
        assert.deepEqual(results.filter(result => result.overflow.length), [], 'Content must fit each viewport.');
        assert.deepEqual(errors, [], 'No JavaScript errors.');
        console.log(`PASS: ${results.length} route/layout checks, navigation focus and scroll isolation, chart touch interaction.`);
    } finally {
        await browser.close();
    }
}

run().catch(error => { console.error(error); process.exitCode = 1; });
