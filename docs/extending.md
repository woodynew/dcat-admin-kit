# 二次扩展约定

本页面向准备给 Kit 新增通用能力的维护者。业务专用组件应优先留在业务应用中，只有不依赖具体模型、表结构、路由和业务枚举的能力才适合进入 Kit。

## 新增列显示器

Dcat Admin 的列扩展以 `AbstractDisplayer` 为基础。最小实现如下：

```php
namespace Woodynew\DcatAdminKit\Grid\Displayers;

use Dcat\Admin\Grid\Displayers\AbstractDisplayer;

class StatusText extends AbstractDisplayer
{
    public function display(array $map = [])
    {
        return e($map[$this->value] ?? $this->value);
    }
}
```

在 `Bootstrapper::registerColumnDisplayers()` 中注册别名：

```php
Column::extend('statustext', StatusText::class);
```

业务应用即可像基座列方法一样使用：

```php
$grid->column('status')->statustext([
    0 => '停用',
    1 => '启用',
]);
```

Kit 在 Laravel 服务提供者的 `register()` 阶段注册公共列别名，目的是让列 API 不依赖 Dcat 扩展记录是否已经启用。新增公共列别名时应保持这一特性。

## 新增操作或工具

根据挂载位置选择 Dcat Admin 基类：

| 目标位置 | 推荐基类 | Kit 目录 |
| --- | --- | --- |
| 表单顶部工具 | `Dcat\Admin\Form\Tool` | `src/Actions/Form` |
| Grid 顶部工具 | `Dcat\Admin\Grid\Tools\AbstractTool` | `src/Grid/Tools` |
| Grid 行操作 | `Dcat\Admin\Grid\RowAction` | `src/Grid/RowActions` |
| Grid 行操作展示器 | `Dcat\Admin\Grid\Displayers\Actions` | `src/Grid/Actions` |
| 独立可渲染组件 | `Illuminate\Contracts\Support\Renderable` | `src/Widgets` |

操作类需要前端脚本时，应使用委托事件并带独立命名空间，以兼容 PJAX 重载并避免重复绑定：

```javascript
$('body')
    .off('click.dcatAdminKitExample', '.example-selector')
    .on('click.dcatAdminKitExample', '.example-selector', function (event) {
        event.preventDefault();
    });
```

把 PHP 数据写入 JavaScript 或 HTML 前必须按目标上下文编码，不能直接拼接用户输入。

## 新增全局特性

全局特性统一由 `Bootstrapper` 读取：

```php
if (! $this->enabled('feature_name')) {
    return;
}
```

新增特性必须：

1. 在 `config/dcat-admin-kit.php` 中默认设为 `false`；
2. 明确说明会影响哪些 Dcat Builder 或页面；
3. 确保 `Bootstrapper::boot()` 重复执行不会重复注册监听器或脚本；
4. 补充 Package/Component 测试和本目录文档；
5. 不把隐藏按钮当作权限控制。

## 静态资源

运行时资源必须放在 `resources/assets`，通过扩展或 `dcat-admin-kit-assets` 发布到应用本地目录。不要在 PHP、Blade、CSS 或业务 JavaScript 中新增 CDN 依赖。

引入第三方资源时，应保留许可证头，并在 `THIRD_PARTY_NOTICES.md` 记录来源和许可证。

## 完成检查

```bash
composer test
composer phpstan
composer validate --strict
git diff --check
```

新增或修改公开组件时，还应确认：

- 类能通过 Composer 自动加载；
- 示例中的命名空间、构造参数和真实签名一致；
- 空值、长文本、数组值和特殊字符有明确行为；
- PJAX 后脚本不会重复绑定；
- 可选依赖缺失时有安全退路或清晰错误；
- 对应文档已加入 [`docs/README.md`](README.md) 导航。
