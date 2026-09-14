# Changelog

All notable changes to this project will be documented in this file.

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
