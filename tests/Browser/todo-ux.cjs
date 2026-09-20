// Use a populated isolated local instance; this exercise creates/deletes test tasks.
const assert = require('node:assert/strict');
const fs = require('node:fs/promises');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');
const baseURL = process.env.TODO_REVIEW_URL || 'http://127.0.0.1:8879';
const output = process.env.TODO_REVIEW_OUTPUT || '/tmp/homeplanner-todo-review';

(async () => {
    assert(['localhost', '127.0.0.1', '[::1]'].includes(new URL(baseURL).hostname));
    await fs.mkdir(output, { recursive: true });
    const browser = await chromium.launch({
        executablePath: process.env.CHROMIUM_EXECUTABLE || undefined,
        headless: true, args: ['--no-sandbox'],
    });
    try {
        const page = await browser.newPage({ viewport: { width: 390, height: 844 }, hasTouch: true });
        const errors = [];
        page.on('pageerror', error => { errors.push(error.message); console.error('Browser:', error.message); });
        await page.goto(baseURL + '/login');
        await page.locator('#login-email').fill(process.env.TODO_REVIEW_EMAIL || 'visual@example.test');
        await page.locator('#login-password').fill(process.env.TODO_REVIEW_PASSWORD || 'visual-test-password');
        await page.locator('form button[type=submit]').click();
        await page.waitForURL(baseURL + '/');
        await page.goto(baseURL + '/todo');
        assert(await page.locator('.todo-task-row').count(), 'Populate the isolated instance before running.');

        const results = [];
        for (const dark of [false, true]) {
            await page.evaluate(dark => document.documentElement.classList.toggle('dark', dark), dark);
            for (const width of [320, 360, 390, 430, 640, 768, 820, 1024, 1280, 1440]) {
                await page.setViewportSize({ width, height: 844 });
                await page.waitForTimeout(250);
                const result = await page.evaluate(() => {
                    const visible = element => element.getBoundingClientRect().height && getComputedStyle(element).visibility !== 'hidden';
                    const overflow = [...document.querySelectorAll('.todo-manager *')].filter(visible).filter(element => {
                        const rect = element.getBoundingClientRect();
                        return rect.right > innerWidth + 1 || rect.left < -1;
                    }).map(element => element.className);
                    const clippedTitles = [...document.querySelectorAll('.todo-task-title')].filter(element => element.scrollHeight > element.clientHeight + 1).map(element => element.value);
                    const smallActions = [...document.querySelectorAll('.todo-task-row button, .todo-task-drag, .todo-task-date, .todo-tag-input')].filter(visible).filter(element => {
                        const rect = element.getBoundingClientRect();
                        return rect.width < 44 || rect.height < 44;
                    }).map(element => element.className);
                    return { overflow, clippedTitles, smallActions };
                });
                results.push({ dark, width, ...result });
                assert.deepEqual(result, { overflow: [], clippedTitles: [], smallActions: [] }, JSON.stringify({ width, dark, result }));
                if ([320, 390, 820, 1440].includes(width)) {
                    await page.screenshot({ path: output + '/' + width + (dark ? '-dark' : '-light') + '.png', fullPage: true });
                }
            }
        }

        for (const width of [1440, 820, 390]) {
            await page.setViewportSize({ width, height: 844 });
            const input = page.locator('.todo-tag-input').first();
            await input.scrollIntoViewIfNeeded();
            await page.waitForTimeout(250);
            const before = await input.boundingBox();
            await page.mouse.click(before.x + 15, before.y + before.height / 2);
            await page.locator('.todo-task-tag-options').first().waitFor({ state: 'visible' });
            assert(await input.evaluate(element => element === document.activeElement), 'A single click must focus the tag field');
            const after = await input.boundingBox();
            assert(Math.abs(before.x - after.x) < 1 && Math.abs(before.y - after.y) < 1, 'Opening tags must not move the input');
            await input.press('ArrowDown');
            await page.waitForFunction(() => document.activeElement?.hasAttribute('data-tag'));
            await page.keyboard.press('Escape');
            assert(await input.evaluate(element => element === document.activeElement), 'Escape must return focus to the tag input');
            await page.locator('.todo-task-tag-options').first().waitFor({ state: 'hidden' });
            if (width === 390) await input.tap();
            else await input.click();
            await input.pressSequentially('Gar');
            assert.equal(await input.inputValue(), 'Gar');
            const suggestion = page.locator('.todo-task-tag-options').first().getByRole('button', { name: 'Garden', exact: true });
            await suggestion.click();
            const firstRow = page.locator('.todo-task-row').first();
            await firstRow.getByLabel('Remove tag Garden', { exact: true }).waitFor();
            await firstRow.getByLabel('Remove tag Garden', { exact: true }).click();
            await firstRow.getByLabel('Remove tag Garden', { exact: true }).waitFor({ state: 'detached' });
            await input.click();
            await page.locator('.responsive-title').click();
            await page.locator('.todo-task-tag-options').first().waitFor({ state: 'hidden' });
        }

        await page.setViewportSize({ width: 1440, height: 844 });
        const undated = page.locator('[data-group="no_date"] .todo-task-row');
        assert(await undated.count() >= 2, 'Seed at least two undated tasks to exercise dragging');
        // Exercise and then restore the order, verifying persistence after each drag.
        for (let pass = 0; pass < 2; pass++) {
            const movedId = await undated.nth(1).getAttribute('data-id');
            await undated.first().scrollIntoViewIfNeeded();
            const handle = await undated.nth(1).locator('.todo-task-drag').boundingBox();
            const target = await undated.first().boundingBox();
            await page.mouse.move(handle.x + handle.width / 2, handle.y + handle.height / 2);
            await page.mouse.down();
            await page.mouse.move(target.x + 30, target.y + 15, { steps: 15 });
            await page.waitForTimeout(200);
            await page.mouse.up();
            await page.waitForTimeout(500);
            await page.reload();
            assert.equal(await undated.first().getAttribute('data-id'), movedId, 'Drag order must persist after reload');
        }

        await page.setViewportSize({ width: 390, height: 844 });
        await page.evaluate(() => document.documentElement.classList.remove('dark'));
        await page.locator('.main-fab').click();
        await page.waitForFunction(() => document.activeElement?.classList.contains('todo-task-title') && document.activeElement.value === '');
        const title = page.locator('.todo-task-title:focus');
        await title.waitFor();
        const id = await title.getAttribute('id');
        const itemId = id.replace('task-input-', '');
        const row = () => page.locator('.todo-task-row[data-id="' + itemId + '"]');
        const name = 'Review task with a long title that must wrap across lines and remain completely readable after saving';
        await title.fill(name);
        await title.press('Enter');
        await page.waitForTimeout(250);
        await row().locator('.todo-tag-input').click();
        await row().locator('.todo-tag-input').fill('Visual review');
        await row().locator('.todo-tag-create').click();
        await row().getByLabel('Remove tag Visual review', { exact: true }).waitFor();
        assert(await row().locator('.todo-task-title').evaluate(element => element.scrollHeight <= element.clientHeight + 1), 'Title must stay expanded after Livewire updates');
        const longTag = 'Long tag '.repeat(25).trim();
        await row().locator('.todo-tag-input').click();
        await row().locator('.todo-tag-input').fill(longTag);
        await row().locator('.todo-tag-input').press('Enter');
        await row().getByLabel('Remove tag ' + longTag, { exact: true }).waitFor();
        await row().getByLabel('Remove tag ' + longTag, { exact: true }).click();
        await row().getByLabel('Remove tag ' + longTag, { exact: true }).waitFor({ state: 'detached' });
        await row().locator('.todo-task-date').fill('2030-12-24');
        await page.waitForTimeout(250);
        await page.reload();
        assert.equal(await row().locator('.todo-task-date').inputValue(), '2030-12-24');
        assert.equal(await row().locator('.todo-task-title').inputValue(), name);
        await row().locator('.completion-toggle').click();
        await page.locator('.is-completed[data-id="' + itemId + '"]').waitFor();
        await row().locator('.completion-toggle').click();
        await row().locator('.todo-task-title').waitFor();
        await row().locator('.todo-tag-input').click();
        await row().locator('.todo-tag-input').fill('Visual');
        await row().locator('.todo-tag-input').press('Escape');
        await row().locator('.todo-task-tag-options').waitFor({ state: 'hidden' });
        await row().locator('.todo-tag-input').click();
        await page.waitForTimeout(250);
        await row().screenshot({ path: output + '/tag-picker.png' });
        await row().locator('.todo-tag-done').click();

        await page.locator('.todo-filter-trigger').click();
        await page.locator('.todo-filter-popout').getByLabel('Visual review', { exact: true }).check();
        await page.waitForTimeout(250);
        assert.equal(await page.locator('.todo-task-row').count(), 1);
        await page.locator('.todo-filter-trigger').click();
        await page.locator('.main-fab').click();
        await page.waitForFunction(() => document.activeElement?.classList.contains('todo-task-title') && document.activeElement.value === '');
        assert(await page.locator('.todo-task-row').count() > 1, 'Adding while filtered must reveal the new task');
        const emptyRow = page.locator('.todo-task-row').filter({ has: page.locator('.todo-task-title') }).last();
        page.once('dialog', dialog => dialog.accept());
        await emptyRow.locator('.todo-task-delete').click();
        page.once('dialog', dialog => dialog.accept());
        await row().locator('.todo-task-delete').click();
        await row().waitFor({ state: 'detached' });
        assert.deepEqual(errors, []);
        await fs.writeFile(output + '/report.json', JSON.stringify({ results, errors }, null, 2));
        console.log('PASS: 20 light/dark viewport checks, stable tag-input clicks, keyboard/touch interaction, persistent drag ordering, task editing, tag creation/removal, dates, completion/reopen, filtering and deletion.');
    } finally {
        await browser.close();
    }
})().catch(error => { console.error(error); process.exitCode = 1; });
