const { defineConfig } = require('@playwright/test');

module.exports = defineConfig({
    testDir: './tests/Browser',
    workers: 1,
    retries: 0,
    use: {
        baseURL: 'http://127.0.0.1:18086',
        channel: process.env.CI ? 'chromium' : 'chrome',
        screenshot: 'only-on-failure',
        trace: 'retain-on-failure',
    },
    webServer: {
        command: (process.env.PHP_BINARY || 'php') + ' -S 127.0.0.1:18086 tests/Browser/router.php',
        url: 'http://127.0.0.1:18086',
        reuseExistingServer: false,
    },
});
