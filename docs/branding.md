# Logo and launch icons

`public/logo.svg` is the source for the app logo, browser icons, and mobile launch icons. Its 512×512 intrinsic size also gives browsers a large source when they rasterize an SVG; the Blade logo component still reserves 40×40 or 64×64 CSS pixels for the page layout. The shared head component preloads that same SVG URL.

Use `/logo.svg` in websites, documents, and design tools, preserving its square aspect ratio. In Blade, use `<x-logo size="64" alt="Homeplanner" />`. Omit `alt` when adjacent text already names the application; the default empty alternative text avoids duplicate announcements. `public/favicon.svg` remains a compatibility copy for existing bookmarks and integrations.

`public/site.webmanifest` supplies the SVG and 192×192/512×512 PNG fallbacks for installed app icons and launch screens. Chromium derives its launch screen from the manifest's name, background color, and icons ([web.dev documentation](https://web.dev/articles/add-manifest)). The Apple touch icon is an opaque 180×180 PNG because that platform's home-screen icon uses PNG ([Apple documentation](https://developer.apple.com/library/archive/documentation/AppleApplications/Reference/SafariWebContent/ConfiguringWebApplications/ConfiguringWebApplications.html)). The ICO fallback contains six sizes from 16 to 256 pixels.

After changing the SVG, regenerate the committed assets using an existing Playwright installation:

```sh
PLAYWRIGHT_MODULE=/path/to/playwright node scripts/export-icons.cjs
```

`CHROMIUM_EXECUTABLE` can select an existing Chromium binary. The script renders each PNG directly from the SVG, synchronizes `favicon.svg`, and updates the manifest's icon URL hashes. This is an optional maintenance tool; deployment needs no Node dependencies or asset build.

Page icon links are versioned to refresh browser caches. Existing home-screen shortcuts may retain an OS-cached icon until refreshed or removed and added again. Physical iOS/Android launch screens should be checked on-device; desktop browser emulation only verifies the page logos and icon assets.
