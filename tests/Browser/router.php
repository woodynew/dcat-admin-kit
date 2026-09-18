<?php

// Local test-only renderer: real package output, Bootstrap and jquery-pjax; no database writes.
$root = dirname(__DIR__, 2);
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if (strpos($path, '/assets/') === 0) {
    $assets = realpath($root.'/vendor/woodynew/dcat-laravel-admin/resources/dist');
    $file = realpath($assets.'/'.substr($path, strlen('/assets/')));
    if (! $file || strpos($file, $assets.DIRECTORY_SEPARATOR) !== 0 || ! is_file($file)) {
        http_response_code(404);
        exit;
    }
    $types = ['js' => 'application/javascript', 'css' => 'text/css', 'woff2' => 'font/woff2'];
    header('Content-Type: '.($types[pathinfo($file, PATHINFO_EXTENSION)] ?? 'application/octet-stream'));
    readfile($file);
    exit;
}

require $root.'/vendor/autoload.php';

$app = \Orchestra\Testbench\Foundation\Application::create(null, function ($app) use ($root) {
    $app->afterBootstrapping(\Illuminate\Foundation\Bootstrap\LoadConfiguration::class, function ($app) use ($root) {
        $app['config']->set('admin', require $root.'/vendor/woodynew/dcat-laravel-admin/config/admin.php');
        $app['config']->set('admin.auth.enable', false);
        $app['config']->set('admin.permission.enable', false);
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
    });
}, ['extra' => ['providers' => [
    \Dcat\Admin\AdminServiceProvider::class,
    \Woodynew\DcatAdminKit\DcatAdminKitServiceProvider::class,
], 'dont-discover' => ['*']]]);
$app->setLocale(($_GET['locale'] ?? '') === 'en' ? 'en' : 'zh_CN');
$app->make(\Woodynew\DcatAdminKit\DcatAdminKitServiceProvider::class)->init();

// 表单顶部工具夹具：按真实后台的层级输出（工具在 form 内的 .box-header，原生 button.submit
// 在同一个 form 内），并加载 dcat-app.js 让 $.fn.buttonLoading 是真实实现而不是替身。
if (($_GET['fixture'] ?? '') === 'top-tools') {
    $tools = (new \Woodynew\DcatAdminKit\Actions\Form\TopGoBack)->render()
        .(new \Woodynew\DcatAdminKit\Actions\Form\TopSubmit)->render();
    $asset = \Dcat\Admin\Admin::asset();
    $scripts = new \ReflectionProperty($asset, 'script');
    $scripts->setAccessible(true);
    $script = implode(";\n", array_unique($scripts->getValue($asset)));

    header('Content-Type: text/html; charset=UTF-8');
    ?>
<!doctype html>
<html><head><meta charset="UTF-8"><title>Kit form tools regression</title>
<link rel="stylesheet" href="/assets/dcat/plugins/vendors.min.css">
<link rel="stylesheet" href="/assets/dcat/css/dcat-app.css">
<link rel="stylesheet" href="/assets/adminlte/adminlte.css">
<script src="/assets/dcat/plugins/vendors.min.js"></script>
<script src="/assets/dcat/plugins/jquery-pjax/jquery.pjax.min.js"></script>
</head><body style="padding:40px">
<form id="form-top-tools" action="/?fixture=top-tools" method="post" class="form-horizontal" pjax-container>
    <div class="box-header with-border"><?= $tools ?></div>
    <div class="box-body"><p>Fixture body</p></div>
    <div class="box-footer"><button type="button" class="btn btn-primary submit">提交</button></div>
</form>
<script src="/assets/dcat/js/dcat-app.js"></script>
<script>
window.Dcat = CreateDcat({pjax_container_selector: '#pjax-container', token: 'fixture', colors: {}, lang: {}, dark_mode: false, sidebar_dark: false});
</script>
<script><?= $script ?></script>
</body></html>
    <?php
    exit;
}

$links = '';
foreach (['https://example.com/one', 'https://example.com/?q="<script>alert(1)</script>'] as $index => $value) {
    $grid = \Mockery::mock(\Dcat\Admin\Grid::class);
    $column = \Mockery::mock(\Dcat\Admin\Grid\Column::class);
    $grid->shouldReceive('getKeyName')->andReturn('id');
    $column->shouldReceive('getOriginal')->andReturn($value);
    $displayer = new \Woodynew\DcatAdminKit\Grid\Displayers\CopyQrCodeLink(
        $value, $grid, $column, new \Illuminate\Support\Fluent(['id' => $index + 1])
    );
    $links .= '<p>'.$displayer->display().'</p>';
}
$asset = \Dcat\Admin\Admin::asset();
$scripts = new \ReflectionProperty($asset, 'script');
$scripts->setAccessible(true);
$script = implode(";\n", array_unique($scripts->getValue($asset)));
// Displayer 通过 Admin::style() 注册弹层样式；测试页要按真实后台那样输出，
// 否则暗色适配这类纯样式改动在回归里看不到。
$style = $asset->styleToHtml();
$page = (int) ($_GET['page'] ?? 1);
$fragment = '<h1>Page '.$page.'</h1>'.$links
    .'<a data-fixture-pjax href="/?page='.($page + 1).'">Next via PJAX</a>'
    .$style.'<script>'.$script.'</script>';

header('Content-Type: text/html; charset=UTF-8');
if (! empty($_SERVER['HTTP_X_PJAX'])) {
    echo $fragment;
    exit;
}
?>
<!doctype html>
<html><head><meta charset="UTF-8"><title>Kit QR regression</title>
<link rel="stylesheet" href="/assets/dcat/plugins/vendors.min.css">
<link rel="stylesheet" href="/assets/dcat/css/dcat-app.css">
<link rel="stylesheet" href="/assets/adminlte/adminlte.css">
<link rel="stylesheet" href="/assets/dcat/plugins/extensions/toastr.css">
<script src="/assets/dcat/plugins/vendors.min.js"></script>
<script src="/assets/dcat/plugins/extensions/toastr.min.js"></script>
<script src="/assets/dcat/plugins/jquery-qrcode/dist/jquery-qrcode.min.js"></script>
<script src="/assets/dcat/plugins/jquery-pjax/jquery.pjax.min.js"></script>
</head><body style="padding:80px;min-height:100vh">
<button id="outside">Outside</button>
<button id="other">Other popover</button>
<div id="pjax-container"><?= $fragment ?></div>
<script>
$('#other').popover({title: 'Unrelated', content: 'Other content', trigger: 'manual'});
$(document).pjax('a[data-fixture-pjax]', '#pjax-container');
</script>
</body></html>
