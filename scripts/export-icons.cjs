// Optional maintenance tool; use an existing Playwright installation. No production build is needed.
// PLAYWRIGHT_MODULE=/path/to/playwright node scripts/export-icons.cjs
const fs = require('node:fs/promises');
const path = require('node:path');
const { createHash } = require('node:crypto');
const { chromium } = require(process.env.PLAYWRIGHT_MODULE || 'playwright');

async function run() {
    const publicPath = path.resolve(__dirname, '../public');
    const svg = await fs.readFile(path.join(publicPath, 'logo.svg'), 'utf8');
    const version = createHash('sha256').update(svg).digest('hex').slice(0, 12);
    const browser = await chromium.launch({
        headless: true,
        executablePath: process.env.CHROMIUM_EXECUTABLE || undefined,
        args: ['--no-sandbox'],
    });

    try {
        const page = await browser.newPage();
        const render = async (size, background = null) => Buffer.from(await page.evaluate(async ({ svg, size, background }) => {
            const image = new Image();
            image.src = `data:image/svg+xml;charset=utf-8,${encodeURIComponent(svg)}`;
            await image.decode();
            const canvas = document.createElement('canvas');
            canvas.width = canvas.height = size;
            const context = canvas.getContext('2d');
            if (background) {
                context.fillStyle = background;
                context.fillRect(0, 0, size, size);
            }
            context.drawImage(image, 0, 0, size, size);
            return canvas.toDataURL('image/png').split(',')[1];
        }, { svg, size, background }), 'base64');

        for (const size of [192, 512]) {
            await fs.writeFile(path.join(publicPath, `icon-${size}.png`), await render(size));
        }
        // iOS applies its own rounded mask, so the touch icon has an opaque background.
        await fs.writeFile(path.join(publicPath, 'apple-touch-icon.png'), await render(180, '#2563eb'));
        await fs.writeFile(path.join(publicPath, 'favicon.svg'), svg);

        const sizes = [16, 32, 48, 64, 128, 256];
        const images = [];
        const directory = Buffer.alloc(6 + 16 * sizes.length);
        directory.writeUInt16LE(1, 2);
        directory.writeUInt16LE(sizes.length, 4);
        let offset = directory.length;
        for (const [index, size] of sizes.entries()) {
            const png = await render(size);
            const entry = 6 + index * 16;
            directory[entry] = directory[entry + 1] = size % 256;
            directory.writeUInt16LE(1, entry + 4);
            directory.writeUInt16LE(32, entry + 6);
            directory.writeUInt32LE(png.length, entry + 8);
            directory.writeUInt32LE(offset, entry + 12);
            images.push(png);
            offset += png.length;
        }
        await fs.writeFile(path.join(publicPath, 'favicon.ico'), Buffer.concat([directory, ...images]));

        const manifest = {
            name: 'Homeplanner',
            short_name: 'Homeplanner',
            id: './',
            start_url: './',
            scope: './',
            display: 'standalone',
            background_color: '#f8fafc',
            theme_color: '#2563eb',
            icons: [
                ...[192, 512].map(size => ({ src: `icon-${size}.png?v=${version}`, sizes: `${size}x${size}`, type: 'image/png', purpose: 'any' })),
                { src: `logo.svg?v=${version}`, sizes: 'any', type: 'image/svg+xml', purpose: 'any' },
            ],
        };
        await fs.writeFile(path.join(publicPath, 'site.webmanifest'), JSON.stringify(manifest, null, 4) + '\n');
    } finally {
        await browser.close();
    }
}

run().catch(error => { console.error(error); process.exitCode = 1; });
