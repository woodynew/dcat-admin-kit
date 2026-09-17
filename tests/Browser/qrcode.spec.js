const { test, expect } = require('@playwright/test');

// 弹层只有二维码本身：没有标题栏，也没有关闭按钮。
// 关闭方式为点击弹层外部、按 Escape、再次点击同一个入口，或打开另一个入口。
test('外部点击、Escape、重复打开与单弹层', async ({ page }) => {
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));
    await page.goto('/');
    const triggers = page.locator('.dcat-admin-kit-qrcode');
    const popup = page.locator('.dcat-admin-kit-qrcode-popover');

    await triggers.first().click();
    await expect(popup).toBeVisible();
    await expect(popup.locator('img')).toBeVisible();
    await expect(triggers.first()).toHaveAttribute('aria-expanded', 'true');
    // 标题栏与关闭按钮都不再渲染。
    await expect(popup.locator('.popover-header')).toHaveCount(0);
    await expect(popup.locator('button')).toHaveCount(0);
    await popup.locator('img').click();
    await expect(popup).toBeVisible();

    await triggers.first().click();
    await expect(popup).toHaveCount(0);
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
    await page.locator('h1').click();
    await expect(page.locator('.dcat-admin-kit-qrcode-popover')).toHaveCount(0);
    await page.goBack();
    await expect(page.locator('h1')).toHaveText('Page 1');
    await page.locator('.dcat-admin-kit-qrcode').first().click();
    await page.keyboard.press('Escape');
    await expect(page.locator('.dcat-admin-kit-qrcode-popover')).toHaveCount(0);
    expect(errors).toEqual([]);
});

test('英文入口标签与复制内容保持可用', async ({ page, context }) => {
    await context.grantPermissions(['clipboard-read', 'clipboard-write']);
    await page.goto('/?locale=en');
    const trigger = page.locator('.dcat-admin-kit-qrcode').first();
    await expect(trigger).toHaveAttribute('aria-label', 'QR code');
    await trigger.click();
    await expect(page.locator('.dcat-admin-kit-qrcode-popover img')).toBeVisible();
    await page.locator('h1').click();
    await expect(page.locator('.dcat-admin-kit-qrcode-popover')).toHaveCount(0);
    await page.locator('.dcat-admin-kit-copyable').first().click();
    await expect.poll(() => page.evaluate(() => navigator.clipboard.readText())).toBe('https://example.com/one');
});

test('滚动后打开二维码弹层不会把页面带回顶部', async ({ page }) => {
    await page.goto('/');
    await page.evaluate(() => $('<div>').css('height', '2000px').insertBefore('#pjax-container'));
    const triggers = page.locator('.dcat-admin-kit-qrcode');
    await triggers.first().scrollIntoViewIfNeeded();
    const scrolled = await page.evaluate(() => window.scrollY);
    expect(scrolled).toBeGreaterThan(100);

    await triggers.first().click();
    await expect(page.locator('.dcat-admin-kit-qrcode-popover')).toBeVisible();
    expect(await page.evaluate(() => window.scrollY)).toBe(scrolled);
});

test('弹层只显示二维码图片，暗色下保持白色卡片与配套箭头', async ({ page }) => {
    await page.goto('/');
    for (const dark of [false, true]) {
        await page.evaluate(dark => document.body.classList.toggle('dark-mode', dark), dark);
        await page.locator('.dcat-admin-kit-qrcode').first().click();
        const popup = page.locator('.dcat-admin-kit-qrcode-popover');
        await expect(popup).toBeVisible();
        await expect(popup.locator('.popover-header')).toHaveCount(0);
        await expect(popup.locator('button')).toHaveCount(0);
        const image = await popup.locator('img').evaluate(node => ({
            naturalWidth: node.naturalWidth,
            alt: node.getAttribute('alt'),
        }));
        expect(image.naturalWidth).toBeGreaterThan(0);
        expect(image.alt).toBe('二维码');
        // 二维码的静区要求白底；暗色主题若不覆盖，弹层会和页面背景糊在一起。
        await expect(popup.locator('.popover-body')).toHaveCSS('background-color', 'rgb(255, 255, 255)');
        const arrow = await popup.evaluate(node => {
            const placement = node.getAttribute('x-placement');
            const style = getComputedStyle(node.querySelector('.arrow'), '::after');
            const side = { right: 'borderRightColor', left: 'borderLeftColor', top: 'borderTopColor', bottom: 'borderBottomColor' };
            return { placement, color: style[side[placement.split('-')[0]]] };
        });
        expect(arrow.color, '箭头应与白色卡片同色（' + arrow.placement + '）').toBe('rgb(255, 255, 255)');
        await page.locator('h1').click();
        await expect(popup).toHaveCount(0);
    }
});
+
// 二维码必须自带静区（白边）：无白边渲染在截图、裁切或放到其他背景后会失去静区，影响识别。
// jquery-qrcode 的 quiet 选项按模块计算，4 是标准值。
test('二维码自带静区，图片尺寸仍按调用方要求', async ({ page }) => {
    await page.goto('/');
    await page.locator('.dcat-admin-kit-qrcode').first().click();
    const popup = page.locator('.dcat-admin-kit-qrcode-popover');
    await expect(popup).toBeVisible();

    const measured = await popup.locator('img').evaluate(img => {
        const canvas = document.createElement('canvas');
        canvas.width = img.naturalWidth;
        canvas.height = img.naturalHeight;
        const ctx = canvas.getContext('2d');
        ctx.drawImage(img, 0, 0);
        const data = ctx.getImageData(0, 0, canvas.width, canvas.height).data;
        const w = canvas.width, h = canvas.height;
        const dark = (x, y) => data[(y * w + x) * 4] < 128;
        const whiteRow = y => { for (let x = 0; x < w; x++) if (dark(x, y)) return false; return true; };
        let top = 0; while (top < h && whiteRow(top)) top++;
        return { side: img.naturalWidth, borderPx: top };
    });

    // 默认 200x200：外框尺寸不变，多余的空间留作静区。
    expect(measured.side).toBe(200);
    // 默认带静区；具体宽度由下面「quiet 参数生效」用例逐档核对。
    expect(measured.borderPx).toBeGreaterThan(0);
});
+
// quiet 参数要真的流到渲染层：既不能丢属性回落到默认值，也不能把显式的 0 当成"空值"替换掉。
test('quiet 参数生效：默认 2、可调 4、显式 0 保留', async ({ page }) => {
    await page.goto('/');

    const measureQuiet = async quiet => {
        return page.evaluate(async quiet => {
            $('#probe-trigger').remove();
            const trigger = $('<a href="javascript:void(0)" id="probe-trigger" class="dcat-admin-kit-qrcode">')
                .text('probe')
                .attr('data-text', 'https://example.com/probe')
                .attr('data-width', 200)
                .attr('data-height', 200)
                .attr('data-original-label', 'probe')
                .attr('tabindex', '0');
            // null 用来验证属性缺失时的回落行为。
            if (quiet !== null) trigger.attr('data-quiet', quiet);
            trigger.appendTo('body');
            $('#probe-trigger').trigger('click');
            await new Promise(resolve => setTimeout(resolve, 300));

            const img = document.querySelector('.dcat-admin-kit-qrcode-popover img');
            const canvas = document.createElement('canvas');
            canvas.width = img.naturalWidth;
            canvas.height = img.naturalHeight;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(img, 0, 0);
            const data = ctx.getImageData(0, 0, canvas.width, canvas.height).data;
            const w = canvas.width, h = canvas.height;
            const dark = (x, y) => data[(y * w + x) * 4] < 128;
            const whiteRow = y => { for (let x = 0; x < w; x++) if (dark(x, y)) return false; return true; };
            let top = 0; while (top < h && whiteRow(top)) top++;
            $('#probe-trigger').trigger('click');
            $('#probe-trigger').remove();
            return top;
        }, quiet);
    };

    const zero = await measureQuiet(0);
    const one = await measureQuiet(1);
    const two = await measureQuiet(2);
    const four = await measureQuiet(4);
    const missing = await measureQuiet(null);

    // 显式 0 必须保留：不能被当成"空值"回落到默认 2。
    expect(zero, '显式 data-quiet=0 应无静区').toBe(0);
    // 属性缺失时回落到默认值，结果应与显式 2 完全一致。
    expect(missing, '属性缺失应回落到默认值 2').toBe(two);
    expect(two, '默认应有静区').toBeGreaterThan(0);
    // 参数逐档变宽。用相对比较而不是像素→模块换算：
    // 插件会做居中取整，换算出来的模块数有 ±1 的噪声。
    expect(one).toBeGreaterThan(zero);
    expect(two).toBeGreaterThan(one);
    expect(four).toBeGreaterThan(two);
});

test('width/height 参数生效且始终为正方形', async ({ page }) => {
    await page.goto('/');
    // jquery-qrcode 只支持正方形 size，取宽高较小值。旧实现传的是插件不认识的
    // width/height，任何自定义尺寸都会被忽略、固定渲染成 200。
    for (const [width, height, expected] of [[200, 200, 200], [240, 240, 240], [300, 300, 300], [300, 180, 180]]) {
        const size = await page.evaluate(async ({ width, height }) => {
            $('#probe-trigger').remove();
            $('<a href="javascript:void(0)" id="probe-trigger" class="dcat-admin-kit-qrcode">')
                .text('probe')
                .attr('data-text', 'https://example.com/probe')
                .attr('data-width', width)
                .attr('data-height', height)
                .attr('data-original-label', 'probe')
                .attr('tabindex', '0')
                .appendTo('body');
            $('#probe-trigger').trigger('click');
            await new Promise(resolve => setTimeout(resolve, 300));
            const img = document.querySelector('.dcat-admin-kit-qrcode-popover img');
            const result = img ? img.naturalWidth : 0;
            $('#probe-trigger').trigger('click');
            $('#probe-trigger').remove();
            return result;
        }, { width, height });
        expect(size, 'data-width=' + width + ' data-height=' + height).toBe(expected);
    }
});
+
// 复制成功后给一条短 toast；原来只在点击时弹 tooltip，没有任何完成反馈。
test('复制成功显示短 toast 并自动消失', async ({ page, context }) => {
    await context.grantPermissions(['clipboard-read', 'clipboard-write']);
    await page.goto('/');
    const toast = page.locator('#toast-container > .dcat-admin-kit-toast');
    await expect(toast).toHaveCount(0);

    await page.locator('.dcat-admin-kit-copyable').first().click();
    await expect(toast).toBeVisible();
    await expect(toast).toContainText('已复制');
    // 成功提示不应该同时弹出 tooltip。
    await expect(page.locator('.tooltip')).toHaveCount(0);

    const width = await toast.evaluate(node => Math.round(node.getBoundingClientRect().width));
    expect(width, '提示要小巧，不超过 240px').toBeLessThanOrEqual(240);

    // 短提示：1.5s 后自行消失，不需要用户手动关闭。
    await expect(toast).toHaveCount(0, { timeout: 5000 });
    await expect.poll(() => page.evaluate(() => navigator.clipboard.readText())).toBe('https://example.com/one');
});

test('复制提示跟随语言', async ({ page }) => {
    await page.goto('/?locale=en');
    await page.locator('.dcat-admin-kit-copyable').first().click();
    await expect(page.locator('#toast-container > .dcat-admin-kit-toast')).toContainText('Copied');
});
+
// 卡片尺寸应该正好等于二维码图片：adminlte 默认的 .popover-body 内边距会在
// 二维码自带静区外再多套一圈白边，看起来边框过宽。
test('弹层卡片紧贴二维码，没有多余内边距', async ({ page }) => {
    await page.goto('/');
    await page.locator('.dcat-admin-kit-qrcode').first().click();
    const popup = page.locator('.dcat-admin-kit-qrcode-popover');
    await expect(popup).toBeVisible();

    const measured = await popup.evaluate(node => {
        const body = node.querySelector('.popover-body');
        const img = node.querySelector('img');
        const bb = body.getBoundingClientRect();
        const ib = img.getBoundingClientRect();
        return {
            padX: Math.round(ib.left - bb.left),
            padY: Math.round(ib.top - bb.top),
            bodyW: Math.round(bb.width),
            imgW: Math.round(ib.width),
        };
    });
    expect(measured.padX).toBe(0);
    expect(measured.padY).toBe(0);
    expect(measured.bodyW).toBe(measured.imgW);
    // 去掉内边距后图片会顶到卡片四角，圆角必须仍然生效。
    await expect(popup.locator('.popover-body')).toHaveCSS('overflow', 'hidden');
});
