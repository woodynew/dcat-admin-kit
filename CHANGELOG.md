# Changelog

All notable changes to this project will be documented in this file.

## [0.2.3] - 2026-09-17

- Adapt the grid header, its top and bottom separator lines, and both NiceScroll scrollbars to the dark theme. The dark theme previously left the header light, drew two white separator lines around it, and kept the light scrollbar colors written by the JavaScript initializer.
- Show only the QR code in its popover. The title bar and close button are gone; close it by clicking outside, pressing Escape, or clicking the same trigger again. In dark mode the popover stays a white card with a matching arrow and a dark shadow, and the body padding is removed so the card hugs the image.
- Fix the QR code size options. `width` and `height` are now applied through the plugin's `size` option, which was previously ignored and always rendered 200x200. A new fifth `$quiet` argument sets the quiet zone in modules: the default is 2 to keep the border narrow, while 4 — the value required by the specification and used by ZXing — remains available for print or heavy downscaling.
- Confirm a successful copy with a short toast. The tooltip that showed the raw content on every click is replaced by a compact, auto-dismissing result message.

## [0.2.2] - 2026-09-15

- Keep grid header labels on one line. A translated or long header no longer wraps into a taller header row; the table keeps its width and the column edge scrolls as before.

## [0.2.1] - 2026-09-15

- Stop the QR-code popover from scrolling the page back to the top when it moves focus to its close button. The trigger keeps the page position and the accessible focus transfer.

## [0.2.0] - 2026-09-14

- Add an opt-in locale switcher with session preferences, authenticated CSRF-protected switching, and same-origin iframe shell reloads.
- Translate remaining tool defaults and provide Simplified Chinese, Traditional Chinese and English language packs, including for standalone components.
- Add an accessible QR-code close button, outside-click and Escape dismissal, single-popover behavior, and PJAX cleanup without changing the column API.
- Keep the QR-code header and close button legible in both light and dark themes.

## [0.1.0]

- Initial reusable Dcat Admin components.
- Configurable global Grid, Form, Show, Filter and asset behavior.
- Local-only runtime assets and optional iframe-tab integration.
- Always register public column aliases independently of Dcat extension state.
- Make global bootstrapping idempotent when Laravel and Dcat both initialize the provider.
- Harden HTML and JavaScript output encoding and keep iframe actions functional after PJAX updates.
- Add safe fallback navigation when the optional iframe-tab package is unavailable.
