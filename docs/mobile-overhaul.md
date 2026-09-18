# Mobile overhaul — 18 September 2026

The mobile layout now uses consistent compact breakpoints, financial editors that respond to their card width, and dialogs that follow the visible viewport. The existing history-chart implementation was preserved, with a correction for touch tooltips disappearing after scrolling.

## Changes

- Navigation sheets expose expanded state, contain keyboard focus, disable background interaction, restore focus and scroll position when closed, and fit short landscape screens.
- Safe-area padding, dynamic viewport sizing, and visual-viewport updates keep sheets and controls clear of screen edges and software keyboards. Touch devices do not automatically focus text fields when a dialog opens.
- Shopping and task controls have larger tap areas. Task dates are fully readable, completed rows are more compact, list menus support keyboard activation, and the empty undated group has a visible label while remaining a drag destination.
- Financial editing cards adapt to their available width, including tablets and desktop cards beside the sidebar. Expense snapshots use compact rows with the name and amount above secondary details; balance snapshots use two-column mobile layouts; the bounded snapshot list scrolls without crushing its cards.
- Snapshot selection and deletion use separate buttons. Chart tooltips open after a completed tap and remain visible when touch generates a leave event.
- Kids approval cards, photo actions, admin controls, long translated buttons, email lists, sign-in, and setup forms fit narrow screens. Theme controls reflect the browser's initial theme.
- Shared UI listeners initialize once across Livewire navigation. Attribute-only updates avoid rescanning every form control during animations and chart interaction.

## Verification

All checks used separate SQLite test databases with synthetic household data. The application database and environment configuration were not changed.

| Check | Result |
| --- | --- |
| Laravel feature and unit tests | 89 passed, 329 assertions |
| Browser route/layout matrix | 154 checks passed: 12 routes plus economy/savings edit modes at 11 widths |
| Widths | 320, 360, 390, 430, 640, 700, 768, 820, 1024, 1280, 1440 CSS pixels |
| Short screens | Navigation and five kids dialogs at 320×568, 390×400, and 820×390 |
| Main flows | Shopping add/edit/quantity/completion, todo add/date/tag/complete/reopen, income/savings edits persisted after reload |
| Touch sorting | Shopping items reordered through touch events; order persisted after reload |
| Forms | Member creation and chore assignment submitted at 320×450; Google settings controls fit |
| Localization and appearance | All 12 routes in Swedish dark mode at 320px; 125% CSS zoom; language and theme switching |
| Authentication | Sign-in and both manual/Google setup forms checked at 320px |
| Navigation/accessibility | Livewire navigation, sheet links, focus containment, Escape, focus restoration, background isolation |
| History | Snapshot button/keyboard selection and touch tooltips after scrolling |
| Browser errors | No JavaScript errors in the final audited flows |

Browser verification used Chromium with touch emulation. Short viewport tests simulate restricted keyboard space; physical iOS/Android keyboards and Safari were not available in this environment. External Google OAuth credentials/redirects were not exercised.

## Repeat the layout audit

The regression script is `tests/Browser/mobile-audit.cjs`. It uses an existing Playwright installation; no production build step or application dependency was added.

Start an isolated local instance with an administrator, enabled modules, shopping/task entries, financial records, children/chores, and history snapshots. Pass its URL and credentials through the environment:

```sh
MOBILE_AUDIT_URL=http://127.0.0.1:8876 \
MOBILE_AUDIT_EMAIL=audit@example.test \
MOBILE_AUDIT_PASSWORD=audit-password-2026 \
PLAYWRIGHT_MODULE=/path/to/existing/node_modules/playwright \
node tests/Browser/mobile-audit.cjs
```

`CHROMIUM_EXECUTABLE` optionally selects an existing browser binary. `MOBILE_AUDIT_OUTPUT` sets the screenshot and JSON report directory (default `/tmp/homeplanner-mobile-review`). The script rejects non-local hosts. The chart interaction check requires populated history to run.

Run the application suite with `php artisan test`. The verification environment used `LOG_CHANNEL=stderr` because its existing daily log file was owned by the web-server account.
