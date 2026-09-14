# 多语言

Kit 提供可选的后台语言切换器，默认关闭。翻译沿用 Laravel 与 Dcat 的语言包，不创建用户字段或翻译数据表。

本功能自 `0.2.0` 起提供，`0.1.0` 及更早版本不包含语言切换与 Kit 语言包。本地挂载源码联调时，锁文件中的版本号不会随源码变化，不要仅凭 Composer 显示的版本号判断已加载的实现。

## 启用

先按[安装与启用](installation.md)安装并启用 Kit 扩展，再在应用的 `config/dcat-admin-kit.php` 中合并：

```php
'features' => [
    // 保留已有开关
    'locale_switcher' => true,
],
'locale' => [
    'locales' => ['zh_CN' => '简体中文', 'zh_TW' => '繁體中文', 'en' => 'English'],
    'session_key' => 'dcat-admin-kit.locale',
],
```

更新已有配置时手工合并即可，不需要覆盖整份配置。使用配置或路由缓存的应用需要重新生成相应缓存。

后台右上角会出现语言选择框。它通过 `POST <后台前缀>/kit/locale` 保存当前 Session 的选择，保留后台登录认证和 `web` 组的 CSRF 校验；普通管理员无需额外菜单权限。支持的语言以 `locale.locales` 为白名单，非法值返回 422；功能关闭时接口不可用。

使用默认的 `admin.route.middleware = ['web', 'admin']`。语言中间件自动加到 `admin` 组前部，在后台启动文件、控制器和视图运行前设置 Laravel 当前语言及 `app.locale`，因此同一 Session 的普通页面、AJAX、PJAX 和同源 iframe 请求都能跟随。自定义中间件组时，需要确保 Session 已启动，并在后台启动逻辑之前执行 `Woodynew\DcatAdminKit\Http\Middleware\SetLocale`。

未选择语言或已保存的语言被移出白名单时，沿用应用当前语言；缺失翻译沿用 Laravel 的 `app.fallback_locale`。Session 到期或被清除后，偏好随之丢失。未安装 Kit 切换器的项目仍可用 `app()->setLocale()` 使用各包的翻译。

## iframe 联动

使用支持多语言的 `woodynew/z-dcat-iframe-tab` 版本，并重新发布 iframe 静态资源：

```bash
php artisan vendor:publish --tag=iframe-tab --force
```

若以前发布过 iframe 视图到 `resources/views/vendor/iframe-tab`，需要手工合并新视图的翻译调用与 `iframe-tab-i18n` 数据块，避免旧视图覆盖包内新视图。

语言切换成功后刷新同源顶层页面。iframe 扩展发现语言变化会清除旧语言的标签缓存，关闭已打开标签并重新打开首页；首次升级也会丢弃没有语言标记的旧缓存。同语言刷新保留原有缓存行为。请先保存编辑内容，再切换语言。

iframe 扩展不依赖 Kit；它读取 Laravel 当前语言，自行维护文案与缓存。跨域嵌入无法共享这个同源刷新流程，需要宿主应用另行处理。

## 各包与业务项目的责任

- Dcat 核心语言包：`admin.*`，按 Dcat 安装流程发布 `dcat-admin-lang`。
- Kit 语言包：`woodynew.dcat-admin-kit::kit.*`，内置简中、繁中、英文。
- iframe 语言包：`iframe-tab::iframe.*`，内置简中、繁中、英文。
- 业务项目：维护菜单、页面标题、字段、枚举及业务提示的翻译；Kit 不会自动翻译写死的字符串。

新增扩展应在自己的语言包里保存文案，并使用带命名空间的翻译键；前端提示通过安全的 JSON 编码接收当前语言文案。不要在 Kit 中集中保存其他扩展的文案。

商品名称、文章正文等业务数据的多语言存储不属于此功能。新增语言还需补齐各包及前端插件的对应语言资源。

## 开发组件的语言包

Kit 文案保存在 `resources/lang/<locale>/kit.php`。新增文案时同步维护 `zh_CN`、`zh_TW` 和 `en` 的相同键，例如：

```php
// resources/lang/en/kit.php（保留其他已有条目）
return ['export' => 'Export'];
```

```php
// 组件运行时获取文案
$label = trans('woodynew.dcat-admin-kit::kit.export');
```

Blade 使用 `{{ trans('woodynew.dcat-admin-kit::kit.export') }}`；PHP 拼接 HTML 时使用 `e($label)`。不要在配置缓存、静态属性或服务提供者注册阶段提前计算当前语言的显示文案，应在请求语言确定后构造或渲染组件。组件接受调用方自定义标题时，应保留该标题，仅翻译默认值。

应用覆盖 Kit 文案时，在 Laravel 实际语言目录中创建 `vendor/woodynew.dcat-admin-kit/<locale>/kit.php`，返回需要覆盖的键值。实际目录以 `app()->langPath()` 为准，不要直接修改 `vendor` 内文件。

增加一种可选语言，需要同时：

1. 在 `locale.locales` 中增加语言代码及其本地名称；
2. 补齐 Kit、Dcat、所用扩展和业务应用的语言包；
3. 检查日期、编辑器等前端插件是否支持该语言代码及资源；
4. 验证首次进入、缺失翻译回退、切换及刷新后的显示。

仅修改白名单不会产生翻译。

## 应用菜单、标题与字段

业务代码要显式调用 Laravel 翻译接口，例如：

```php
$grid->column('title', __('demo.fields.title'));
$content->title(__('demo.pages.records'));
```

Dcat 菜单使用 `menu.titles.*`。菜单标题的键由原始标题去掉首尾空白、转为小写并将空格替换成下划线产生。例如数据库菜单标题为 `Orders`，可在应用的 `en/menu.php` 中定义：

```php
return ['titles' => ['orders' => 'Orders']];
```

其他语言维护同一个键，菜单记录本身无需改写。现有中文菜单也可以通过对应的 `menu.titles.原始中文标题` 翻译。不要将中文标签无条件注册到 `app()->getLocale()` 指向的语言中，否则会覆盖英文等语言的预期文案。

## 前端与路由约定

JavaScript 的提示文本通过服务端翻译后传入；不能只翻译 Blade、保留 JS 中写死的中文。嵌入脚本或 JSON 数据块时使用：

```php
json_encode($messages, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
```

普通 HTML 属性使用 Blade 的转义输出。PJAX 后应使用有命名空间的委托事件，避免重复绑定；选择语言时刷新同源顶层页面，以重新生成整页文案。

Kit 路由文件处于 Dcat 已带名称前缀的路由组中，因此定义时使用相对名称 `kit.locale`，不要重复拼接 `admin_route_name()`；在权限判断或引用已注册路由时再使用 `admin_route_name('kit.locale')`。应用若有额外的访问白名单，需要允许此命名路由的 POST 请求，且保留登录、CSRF 与语言白名单验证。

## 验收与回归

```bash
composer test
composer phpstan
```

应用层还需要验证可见菜单、页面标题、表头和操作提示，而不仅是 `<html lang>` 或选择框值。检查三种语言、普通页面/PJAX/iframe、重新进入后的偏好、非法语言及登录与 CSRF 保护。语言选择框应检查文字高度、下拉标识和长标签在实际导航栏中的显示，避免叠加 `form-control-sm` 后裁切。
