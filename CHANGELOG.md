# Changelog

All notable changes to this project will be documented in this file.

## [Unreleased]

## [0.1.0]

- Initial reusable Dcat Admin components.
- Configurable global Grid, Form, Show, Filter and asset behavior.
- Local-only runtime assets and optional iframe-tab integration.
- Always register public column aliases independently of Dcat extension state.
- Make global bootstrapping idempotent when Laravel and Dcat both initialize the provider.
- Harden HTML and JavaScript output encoding and keep iframe actions functional after PJAX updates.
- Add safe fallback navigation when the optional iframe-tab package is unavailable.
