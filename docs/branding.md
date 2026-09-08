# Homeplanner logo

`public/logo.svg` is the standalone master logo: the existing white calendar on a blue rounded square. It contains only vector shapes, with no fonts, embedded bitmaps, or external dependencies, and scales to any size without pixelation.

The sidebar, authentication pages, and favicon all use this same file. Browser URLs include its modification time so updated artwork is refreshed automatically.

Use `/logo.svg` in websites, documents, and design tools. Keep the square aspect ratio. For example:

```html
<img src="/logo.svg" width="160" height="160" alt="Homeplanner">
```

In Blade, use `<x-logo size="64" alt="Homeplanner" />`. Omit `alt` when adjacent text already names the application; the default empty alternative text avoids duplicate announcements.

`public/favicon.svg` is a compatibility copy for existing bookmarks and integrations. After editing the master, update this copy with `cp public/logo.svg public/favicon.svg`. New integrations should use `logo.svg`.
