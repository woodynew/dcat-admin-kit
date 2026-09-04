# 安装与启用

## 环境要求

- PHP 7.4 或 PHP 8.x；
- Laravel 8～12；
- `woodynew/dcat-laravel-admin` 2.2.4 或更高版本；
- PHP 扩展 `json`、`mbstring`；
- Dcat Admin 已完成基础安装，目标数据库中已存在扩展表。

新项目建议使用仍处于官方支持期的 Laravel 版本。Laravel 8～11 主要用于兼容已有项目，Composer 的当前安全策略可能会阻止从零解析旧版本依赖图。

## 安装步骤

### 1. 安装 Composer 包

```bash
composer require woodynew/dcat-admin-kit
```

Laravel 会通过 Composer 自动发现以下服务提供者，无需手动写入 `config/app.php`：

```php
Woodynew\DcatAdminKit\DcatAdminKitServiceProvider::class
```

### 2. 确认 Dcat Admin 已安装

Kit 的安装命令只检查 Dcat Admin 的扩展表，不会替应用执行基础安装。如果尚未安装 Dcat Admin，应先确认当前数据库连接，再按基座流程执行：

```bash
php artisan admin:install
```

该命令会修改数据库，只应在确认目标环境和数据库后执行。

### 3. 安装并启用 Kit

```bash
php artisan dcat-admin-kit:install
```

此命令会：

- 检查 `admin.database.connection` 指向的连接，未配置时使用 `database.default`；
- 检查 Dcat Admin 扩展表是否存在；
- 发布 `config/dcat-admin-kit.php`，且不覆盖已有配置；
- 通过 Dcat 扩展管理器安装或更新 Kit 版本与本地资源；
- 启用 `woodynew/dcat-admin-kit` 扩展。

命令可以重复执行。Kit 不拥有菜单记录、业务表或业务数据，因此卸载逻辑不会删除这些内容。

安装成功时会输出：

```text
Dcat Admin Kit installed and enabled. Global features remain disabled until configured.
```

## 安装后的文件

```text
config/dcat-admin-kit.php
public/vendor/dcat-admin-extensions/woodynew/dcat-admin-kit/
├── css/
├── images/
└── js/
```

需要单独重新发布时，可以使用 Laravel 的发布标签：

```bash
# 配置文件；默认不覆盖已有文件
php artisan vendor:publish --tag=dcat-admin-kit-config

# 静态资源
php artisan vendor:publish --tag=dcat-admin-kit-assets
```

只有明确要用包内版本覆盖应用现有副本时，才为 `vendor:publish` 增加 `--force`。

## 注册时机

Kit 有两类能力，注册时机不同：

- `copyqrcodelink`、`multirow`、`textalert`、`afterlimit` 四个列别名随 Laravel 服务提供者加载，即使 Dcat 扩展记录尚未启用也可用；
- 全局特性由 Dcat 扩展初始化流程加载，需要扩展处于启用状态，并在 `config/dcat-admin-kit.php` 中显式开启。

## 最小验证

在任意 Grid 中临时接入一个列显示器：

```php
$grid->column('url')->copyqrcodelink();
```

打开页面后应看到“二维码”和“复制”入口。若开启了全局特性但页面没有变化，依次检查：

1. Dcat 扩展管理页面中的 Kit 扩展是否启用；
2. `config/dcat-admin-kit.php` 中对应开关是否为 `true`；
3. 应用是否仍在使用旧配置缓存；必要时执行 `php artisan config:clear`；
4. `public/vendor/dcat-admin-extensions/woodynew/dcat-admin-kit` 下的资源是否已发布。

## 常见安装错误

### Dcat Admin 表不存在

```text
Dcat Admin table [admin_extensions] does not exist.
```

这表示当前连接上没有找到 Dcat Admin 扩展表。先核对 `admin.database.connection`、`database.default` 和实际环境，不要在未确认数据库的情况下直接运行安装或迁移命令。

### Kit 未注册

```text
Dcat Admin Kit is not registered. Run composer dump-autoload and try again.
```

先确认 Composer 安装成功，再执行：

```bash
composer dump-autoload
php artisan dcat-admin-kit:install
```
