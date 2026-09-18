const { test, expect } = require('@playwright/test');

// 夹具页（router.php 的 top-tools 分支）按真实后台的层级输出：工具在 form 内的 .box-header，
// 原生 button.submit 在同一个 form 内，dcat-app.js 提供真实的 $.fn.buttonLoading。
// 这里不模拟 Dcat 的表单 AJAX，只模拟它对原生按钮做的 data-loading 切换——顶部按钮的
// loading 正是跟随这个属性。
test.beforeEach(async ({ page }) => {
    await page.goto('/?fixture=top-tools');
});

function topSubmit(page) {
    return page.locator('form#form-top-tools .box-header a').filter({ hasText: '提交' });
}

const loadingOf = handle => handle.evaluate(element => ({
    loading: element.getAttribute('data-loading'),
    button: element.classList.contains('btn-loading'),
    disabled: element.classList.contains('disabled'),
}));

test('顶部提交按钮跟随原生提交的 loading 进入与退出', async ({ page }) => {
    const errors = [];
    page.on('pageerror', error => errors.push(error.message));

    const native = page.locator('form#form-top-tools button.submit');
    const handle = await topSubmit(page).elementHandle();
    expect(await loadingOf(handle)).toEqual({loading: null, button: false, disabled: false});

    // Dcat 在请求开始时给原生按钮加 data-loading。
    await native.evaluate(element => element.setAttribute('data-loading', 'ld-fixture'));
    await expect.poll(() => handle.evaluate(element => element.getAttribute('data-loading'))).not.toBe(null);
    expect((await loadingOf(handle)).button).toBe(true);

    // 请求结束（含校验错误、失败）时 Dcat 移除该属性，顶部按钮必须一起复位。
    await native.evaluate(element => element.removeAttribute('data-loading'));
    await expect.poll(() => loadingOf(handle)).toEqual({loading: null, button: false, disabled: false});

    expect(errors).toEqual([]);
});

test('顶部提交按钮空转时转发点击，loading 中不重复触发原生提交', async ({ page }) => {
    const native = page.locator('form#form-top-tools button.submit');
    await native.evaluate(element => {
        window.__nativeClicks = 0;
        element.addEventListener('click', () => window.__nativeClicks++);
    });

    const handle = await topSubmit(page).elementHandle();
    await handle.evaluate(element => element.click());
    expect(await page.evaluate(() => window.__nativeClicks)).toBe(1);

    await native.evaluate(element => element.setAttribute('data-loading', 'ld-fixture'));
    await expect.poll(() => handle.evaluate(element => element.getAttribute('data-loading'))).not.toBe(null);
    await handle.evaluate(element => element.click());
    expect(await page.evaluate(() => window.__nativeClicks)).toBe(1);
});

test('顶部返回按钮在真的会返回时进入 loading，并在 bfcache 恢复时复位', async ({ page }) => {
    // history.back() 在夹具里会真的离开页面：换成计数替身，只验证按钮状态与调用。
    await page.evaluate(() => {
        history.pushState({}, '', location.href);
        window.__backed = 0;
        history.back = () => window.__backed++;
    });

    const handle = await page.locator('form#form-top-tools .box-header a').filter({ hasText: '返回' }).elementHandle();
    await handle.evaluate(element => element.click());
    expect(await page.evaluate(() => window.__backed)).toBe(1);
    expect((await loadingOf(handle)).button).toBe(true);

    // 从浏览器回退缓存恢复页面不会重新执行脚本，pageshow 必须把 loading 复位。
    await page.evaluate(() => window.dispatchEvent(new PageTransitionEvent('pageshow', {persisted: true})));
    await expect.poll(() => loadingOf(handle)).toEqual({loading: null, button: false, disabled: false});
});
