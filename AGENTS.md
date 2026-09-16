# AGENTS.md

面向 [`woodynew/dcat-laravel-admin`](https://github.com/woodynew/dcat-admin) 的可复用 Composer 扩展包，已发布 Packagist，被多个下游应用消费，改动会影响下游。

## 兼容范围

包声明支持 PHP 7.4+ 与 Laravel 8–12，CI 按该矩阵测试。不要为了本地方便抬高 `composer.json` 的下限。

## 命令

```bash
composer install

vendor/bin/phpunit                              # 单元测试
vendor/bin/phpstan analyse --memory-limit=1G    # 静态分析，composer check 会跑前两者

npm ci
PHP_BINARY=<可安装 Testbench 10 的 PHP> npm run test:browser
```

`PHP_BINARY` 由 `playwright.config.js` 读取，默认 `php`；浏览器回归需要 Node.js 22，CI 用 PHP 8.4 + Playwright Chromium。`test-results/`、`playwright-report/`、`composer.lock`、`vendor/`、`node_modules/` 已在 `.gitignore`。

## 版本号与发布

版本号的唯一来源是 `version.php` 的**最后一个键**（Dcat `VersionManager::getLatestFileVersion` 取数组末项），不是 `composer.json`。加小版本必须同时改三处：

1. `version.php` 追加新版本键与条目
2. `CHANGELOG.md` 增加对应小节（最新在上）
3. `tests/PackageTest.php` 里 `admin_extensions` 的 `version` 断言

发布顺序：提交并推送代码 → 打附注标签（`git tag -a X.Y.Z -F -`，与既有标签写法一致）→ 单独 `git push origin refs/tags/X.Y.Z` → 确认 Packagist 收录后再升级下游引用：

```bash
curl -s https://repo.packagist.org/p2/woodynew/dcat-admin-kit.json
```

## 浏览器测试夹具必须对齐真实后台

`tests/Browser/router.php` 是测试夹具，把 `/assets/*` 转发到 `vendor/woodynew/dcat-laravel-admin/resources/dist`。夹具加载的样式和脚本必须与真实后台一致：曾因漏载 `adminlte/adminlte.css`（`.popover{top:0;left:0}` 的来源）让弹层滚动的断言**假通过**。新增依赖 Bootstrap 组件几何或样式的断言前，先确认夹具确实加载了对应资源。

## 静态资源

`resources/assets/**` 会发布到消费者的 `public/vendor/dcat-admin-extensions/woodynew/dcat-admin-kit`。改动这里需要下游重新 publish 并提交产物；只改 PHP 逻辑则不需要。

## 提交

中文提交信息，`type: 描述` 前缀（feat / fix / chore / docs）。提交前用 `git status --short` 与 `git diff --check` 确认，只暂存与本次改动相关的文件。

## 文档

使用者文档在 `docs/`，从 [docs/README.md](docs/README.md) 开始，中文；根目录 `README.md` 英文在前，用于 Packagist 展示。
