const { test, expect } = require('@playwright/test');

test('关闭按钮、外部点击、Escape、重复打开与单弹层', async ({ page }) => {
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    await page.goto('/');
    const triggers = page.locator('.dcat-admin-kit-qrcode');
    const popup = page.locator('.dcat-admin-kit-qrcode-popover');

    await triggers.first().click();
    await expect(popup).toBeVisible();
    await expect(popup.locator('img')).toBeVisible();
    await expect(triggers.first()).toHaveAttribute('aria-expanded', 'true');
    await expect(popup.getByRole('button', { name: '关闭二维码' })).toBeFocused();
    await popup.locator('img').click();
    await expect(popup).toBeVisible();
    await popup.getByRole('button', { name: '关闭二维码' }).click();
    await expect(popup).toHaveCount(0);
    await expect(triggers.first()).toBeFocused();
    await expect(triggers.first()).toHaveAttribute('aria-expanded', 'false');
    await expect(triggers.first()).not.toHaveAttribute('aria-describedby');

    await triggers.first().press('Enter');
    await expect(popup).toBeVisible();
    await page.keyboard.press('Escape');
    await expect(popup).toHaveCount(0);
    await expect(triggers.first()).toBeFocused();

    await triggers.first().click();
    await page.locator('#outside').click();
    await expect(popup).toHaveCount(0);
    await triggers.first().click();
    await triggers.first().click();
    await expect(popup).toHaveCount(0);

    await triggers.first().click();
    await triggers.nth(1).click();
    await expect(popup).toHaveCount(1);
    await expect(triggers.first()).toHaveAttribute('aria-expanded', 'false');
    await expect(triggers.nth(1)).toHaveAttribute('aria-expanded', 'true');
    await expect(page.locator('#pjax-container script[src], #pjax-container img')).toHaveCount(0);
    await page.keyboard.press('Escape');
    await expect(triggers.nth(1)).toBeFocused();
    expect(errors).toEqual([]);
});

test('PJAX 替换前清理，返回后可继续开关且不影响其他 popover', async ({ page }) => {
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    await page.goto('/');
    await page.evaluate(() => { window.fixtureMarker = true; $('#other').popover('show'); });
    await page.locator('.dcat-admin-kit-qrcode').first().click();
    await page.keyboard.press('Escape');
    await expect(page.getByText('Other content', { exact: true })).toBeVisible();
    await page.evaluate(() => $('#other').popover('hide'));
    // Let the actual PJAX event clean up, without an outside mouse click hiding it first.
    await page.locator('.dcat-admin-kit-qrcode').first().click();
    await page.evaluate(() => {
        $(document).one('pjax:beforeReplace.fixture', () => {
            window.cleanedBeforeReplace = $('.dcat-admin-kit-qrcode-popover').length === 0;
        });
        $.pjax({ url: '/?page=2', container: '#pjax-container' });
    });
    await expect(page.locator('h1')).toHaveText('Page 2');
    expect(await page.evaluate(() => window.fixtureMarker)).toBe(true);
    expect(await page.evaluate(() => window.cleanedBeforeReplace)).toBe(true);
    await expect(page.locator('.dcat-admin-kit-qrcode-popover')).toHaveCount(0);
    await page.locator('.dcat-admin-kit-qrcode').first().click();
    await page.getByRole('button', { name: '关闭二维码' }).click();
    await expect(page.locator('.dcat-admin-kit-qrcode-popover')).toHaveCount(0);
    await page.goBack();
    await expect(page.locator('h1')).toHaveText('Page 1');
    await page.locator('.dcat-admin-kit-qrcode').first().click();
    await page.keyboard.press('Escape');
    await expect(page.locator('.dcat-admin-kit-qrcode-popover')).toHaveCount(0);
    expect(errors).toEqual([]);
});

test('英文关闭按钮与复制内容保持可用', async ({ page, context }) => {
    await context.grantPermissions(['clipboard-read', 'clipboard-write']);
    await page.goto('/?locale=en');
    await page.locator('.dcat-admin-kit-qrcode').first().click();
    await page.getByRole('button', { name: 'Close QR code' }).click();
    await page.locator('.dcat-admin-kit-copyable').first().click();
    await expect.poll(() => page.evaluate(() => navigator.clipboard.readText())).toBe('https://example.com/one');
});

test('标题与关闭按钮在浅色和深色主题下保持可读对比度', async ({ page }) => {
    await page.goto('/');
    for (const dark of [false, true]) {
        await page.evaluate(dark => document.body.classList.toggle('dark-mode', dark), dark);
        await page.locator('.dcat-admin-kit-qrcode').first().click();
        const contrast = await page.locator('.dcat-admin-kit-qrcode-popover').evaluate(popup => {
            const header = getComputedStyle(popup.querySelector('.popover-header'));
            const close = getComputedStyle(popup.querySelector('.dcat-admin-kit-qrcode-close'));
            const rgb = value => value.match(/[\d.]+/g).slice(0, 3).map(Number);
            const luminance = channels => channels.map(value => {
                value /= 255;
                return value <= 0.04045 ? value / 12.92 : Math.pow((value + 0.055) / 1.055, 2.4);
            }).reduce((sum, value, index) => sum + value * [0.2126, 0.7152, 0.0722][index], 0);
            const background = rgb(header.backgroundColor);
            const ratio = (color, opacity = 1) => {
                const foreground = rgb(color).map((value, index) => value * opacity + background[index] * (1 - opacity));
                const levels = [luminance(foreground), luminance(background)].sort((a, b) => b - a);
                return (levels[0] + 0.05) / (levels[1] + 0.05);
            };
            return { title: ratio(header.color), close: ratio(close.color, Number(close.opacity)) };
        });
        expect(contrast.title).toBeGreaterThanOrEqual(4.5);
        expect(contrast.close).toBeGreaterThanOrEqual(3);
        await page.getByRole('button', { name: '关闭二维码' }).click();
        await expect(page.locator('.dcat-admin-kit-qrcode-popover')).toHaveCount(0);
    }
});
