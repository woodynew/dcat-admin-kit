# Dcat Admin Kit

Reusable, project-neutral extensions for [`woodynew/dcat-laravel-admin`](https://github.com/woodynew/dcat-admin).

## Requirements

- PHP 7.4 or PHP 8.x
- Laravel 8–12
- `woodynew/dcat-laravel-admin` 2.2.4+

Laravel 8–11 are retained for existing applications and may be blocked by current Composer security policy when resolving a brand-new dependency graph. New applications should use a currently supported Laravel release.

## Installation

```bash
composer require woodynew/dcat-admin-kit
php artisan dcat-admin-kit:install
```

The install command:

- verifies that Dcat Admin has already been installed;
- publishes the Kit configuration without overwriting an existing file;
- installs the Dcat extension version and local assets;
- enables the extension;
- never runs `admin:install` or creates business tables.

## Components

完整的中文使用文档请从 [`docs/README.md`](docs/README.md) 开始，内容包括安装、全局配置、每个组件的参数与接入示例，以及二次扩展约定。

| Namespace | Components |
| --- | --- |
| `Actions\Form` | `Copy`, `TopGoBack`, `TopSubmit` |
| `Grid\Actions` | `TextActions` |
| `Grid\Displayers` | `AfterLimit`, `CopyQrCodeLink`, `MultiRow`, `TextAlert` |
| `Grid\Tools` | `AdminGridHrefTool`, `GridFormTool` |
| `Grid\RowActions` | `OpenIFrameTab`, `GridModalRowAction` |
| `Widgets` | `PostTable` |
| `Support` | `AdminFormUtil` |

The following Dcat column aliases are registered whenever the package provider is loaded, including before the Dcat extension record is enabled:

```php
$grid->column('url')->copyqrcodelink();
$grid->column('summary')->multirow(['name', 'status']);
$grid->column('payload')->textalert(50);
$grid->column('content')->afterlimit(100);
```

`OpenIFrameTab` integrates with `woodynew/z-dcat-iframe-tab` when it is installed. Without it, the action safely falls back to normal navigation.

## 多语言与扩展开发

`0.2.0` 起提供默认关闭的语言切换器 `features.locale_switcher`，内置简体中文、繁體中文和 English。选择保存在当前 Session 中，后台页面、AJAX、PJAX 与同源 iframe 请求跟随 Laravel 当前语言。

Kit 负责切换与自身组件文案，各扩展和应用维护自己的翻译；切换器不会自动翻译写死的菜单、页面标题或业务数据。公共组件的语言包不依赖全局特性是否开启。

- [多语言接入与开发](docs/localization.md)：启用、添加语言、覆盖翻译、菜单与字段、前端文案和 iframe 联动。
- [二次扩展约定](docs/extending.md)：新增组件时的多语言要求。

该能力自 `0.2.0` 起提供；`0.1.0` 及更早版本不包含语言切换与 Kit 语言包。

## Global behavior

All global behavior is disabled by default. Publish the configuration and enable only what the application needs:

```php
// config/dcat-admin-kit.php
return [
    'features' => [
        'grid_defaults' => false,
        'form_defaults' => false,
        'show_defaults' => false,
        'right_side_filter' => false,
        'top_form_tools' => false,
        'back_to_top' => false,
        'grid_assets' => false,
        'global_styles' => false,
        'locale_switcher' => false,
    ],
];
```

The package never loads runtime assets from a CDN. CSS, JavaScript, views and images are published from the package itself. The bundled NiceScroll file retains its MIT license header and is documented in [THIRD_PARTY_NOTICES.md](THIRD_PARTY_NOTICES.md).

## Development

```bash
composer update --prefer-dist
composer test
composer phpstan
composer validate --strict
```

二维码交互回归使用真实 Bootstrap、二维码插件及 jquery-pjax，测试页直接渲染当前 Kit 源码，不依赖 Demo 的已发布版本，也不写入业务数据库：

```bash
npm ci
# 本机已安装 Chrome；PHP_BINARY 可以指定 PHP 8.4 的可执行文件
PHP_BINARY=php84 npm run test:browser
```

浏览器测试需要 Node.js 22，以及支持 `Orchestra Testbench 10` 的 PHP/Laravel 开发依赖。CI 使用 PHP 8.4、Laravel 12 和 Playwright Chromium；组件本身的 PHP 7.4 / Laravel 8–12 兼容矩阵保持不变。

CI tests these combinations:

- Laravel 8 on PHP 7.4 and 8.0
- Laravel 9 on PHP 8.0
- Laravel 10 on PHP 8.1
- Laravel 11 on PHP 8.2
- Laravel 12 on PHP 8.3

The legacy compatibility jobs disable Composer's security blocking only while resolving their test matrix. This does not change consumer Composer policy and does not mark vulnerable Laravel versions as recommended.

## License

[MIT](LICENSE)
