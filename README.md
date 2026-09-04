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

CI tests these combinations:

- Laravel 8 on PHP 7.4 and 8.0
- Laravel 9 on PHP 8.0
- Laravel 10 on PHP 8.1
- Laravel 11 on PHP 8.2
- Laravel 12 on PHP 8.3

The legacy compatibility jobs disable Composer's security blocking only while resolving their test matrix. This does not change consumer Composer policy and does not mark vulnerable Laravel versions as recommended.

## License

[MIT](LICENSE)
