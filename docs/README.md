# Dcat Admin Kit 使用文档

Dcat Admin Kit 是面向 `woodynew/dcat-laravel-admin` 的通用扩展包，提供可单独使用的列显示器、表单工具、Grid 工具、行操作、组件与辅助方法，也提供一组默认关闭的全局界面特性。

本文档以 Dcat Admin Kit `0.2.x` 和 `woodynew/dcat-laravel-admin` `2.2.4+` 为基线。

## 从这里开始

1. [安装与启用](installation.md)：安装前提、安装命令、发布资源、验证与常见问题。
2. [全局特性](global-features.md)：9 个配置开关的实际影响和推荐启用方式。
3. [Grid 列显示器](column-displayers.md)：二维码与复制、多字段合并、长文本弹窗、尾部截取。
4. [操作与工具](actions-and-tools.md)：表单顶部工具、Grid 行操作、Grid 顶部工具和全局操作样式。
5. [组件与辅助工具](widgets-and-support.md)：`PostTable` 与 `AdminFormUtil`。
6. [二次扩展约定](extending.md)：新增列扩展、操作类或全局特性时应遵循的接入方式。
7. [多语言](localization.md)：语言切换、Session 偏好、iframe 联动与语言包约定。

## 快速安装

应用已经安装 Dcat Admin 后执行：

```bash
composer require woodynew/dcat-admin-kit
php artisan dcat-admin-kit:install
```

安装命令会注册并启用扩展、发布配置和本地静态资源。Kit 不会执行 `admin:install`，也不会创建业务表。

所有全局特性默认关闭；4 个列显示器别名则会在 Laravel 加载服务提供者时自动注册，可以直接使用：

```php
$grid->column('url')->copyqrcodelink();
$grid->column('summary')->multirow(['name', 'status']);
$grid->column('payload')->textalert(50);
$grid->column('content')->afterlimit(100);
```

## 组件地图

| 使用位置 | 组件 | 接入入口 |
| --- | --- | --- |
| Grid 列 | `CopyQrCodeLink`、`MultiRow`、`TextAlert`、`AfterLimit` | `$grid->column(...)->别名(...)` |
| 表单顶部工具 | `Copy`、`TopGoBack`、`TopSubmit` | `$form->tools(...)` |
| Grid 默认行操作样式 | `TextActions` | `config/admin.php` |
| Grid 顶部工具 | `AdminGridHrefTool`、`GridFormTool` | `$grid->tools(...)` |
| Grid 行操作 | `OpenIFrameTab`、`GridModalRowAction` | `$grid->actions(...)` |
| 可渲染组件 | `PostTable` | Grid 列 `display` 回调等可渲染位置 |
| 表单辅助判断 | `AdminFormUtil` | Form 事件回调 |

## 推荐接入顺序

先安装并确认扩展可用，再按页面需要接入独立组件。只有确认整个后台都需要统一行为时，才开启对应的全局特性，尤其是 `grid_defaults`、`form_defaults` 和 `global_styles`。

Kit 不从 CDN 加载运行时资源。CSS、JavaScript、视图和图片都来自 Composer 包并发布到应用的 `public/vendor` 目录。

## 与 Dcat Admin 文档的关系

Kit 组件遵循 Dcat Admin 原有的 Grid、Form、Action、Tool 和 Renderable 扩展方式。基础概念和链式调用仍以 Dcat Admin 文档为准，例如：[列的使用和扩展](https://learnku.com/docs/dcat-admin/2.x/use-and-extension-of-columns/8090)。
