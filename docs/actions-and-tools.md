# 操作与工具

本页按实际挂载位置区分表单顶部工具、Grid 顶部工具、Grid 行操作和全局行操作样式。

## 表单顶部工具

### `Copy`

`Copy` 在表单顶部增加一个“复制”入口，点击后跳转到构造函数传入的 URL。具体复制哪些数据、如何把参数带到创建页，由业务控制器处理。

```php
use Woodynew\DcatAdminKit\Actions\Form\Copy;

$form->tools(function (Dcat\Admin\Form\Tools $tools) {
    if ($tools->form()->isEditing()) {
        $url = admin_url('products/create?copy='.$this->getKey());
        $tools->append(new Copy($url));
    }
});
```

构造函数：

```php
new Copy(string $url)
```

该组件只负责生成跳转入口，不会自动复制模型或表单数据。

### `TopGoBack` 与 `TopSubmit`

两者可以通过 `top_form_tools` 全局开启，也可以只挂载到指定表单：

```php
use Woodynew\DcatAdminKit\Actions\Form\TopGoBack;
use Woodynew\DcatAdminKit\Actions\Form\TopSubmit;

$form->tools(function (Dcat\Admin\Form\Tools $tools) {
    $tools->append(new TopGoBack());
    $tools->append(new TopSubmit());
});
```

- `TopGoBack`：在 iframe-tab 环境中优先返回当前活动页；无法识别 iframe-tab 或跨域时退回浏览器历史记录。
- `TopSubmit`：查找当前表单或当前卡片中的第一个 `button.submit`，并触发其点击事件。

如果页面使用完全自定义的提交按钮且没有 `submit` 类，`TopSubmit` 不会自动提交。

## Grid 全局行操作样式

### `TextActions`

`TextActions` 把 Grid 默认的查看、编辑、快速编辑和删除操作改为带状态颜色的文字链接。

在应用的 `config/admin.php` 中配置：

```php
'grid' => [
    'grid_action_class' => Woodynew\DcatAdminKit\Grid\Actions\TextActions::class,
],
```

它只改变操作标签的展示，不改变 Dcat Admin 的路由、权限或操作实现。

## Grid 顶部工具

### `AdminGridHrefTool`

用于在 Grid 工具区增加普通链接按钮。

```php
use Woodynew\DcatAdminKit\Grid\Tools\AdminGridHrefTool;

$grid->tools(function (Dcat\Admin\Grid\Tools $tools) {
    $tools->append(new AdminGridHrefTool(
        '导入记录',
        admin_url('products/imports')
    ));
});
```

构造函数：

```php
new AdminGridHrefTool(?string $title = null, string $href = '')
```

### `GridFormTool`

用于在 Grid 工具区打开一个大尺寸模态框表单。

```php
use App\Admin\Forms\BatchNoticeForm;
use Woodynew\DcatAdminKit\Grid\Tools\GridFormTool;

$grid->tools(function (Dcat\Admin\Grid\Tools $tools) {
    $tools->append(GridFormTool::make(
        '批量通知',
        10,
        BatchNoticeForm::class
    ));
});
```

构造函数：

```php
GridFormTool::make(?string $title = null, $action = 1, ?string $formClass = null)
```

Kit 会通过容器解析 `$formClass`，调用它的 `make(['action' => $action])`，并把返回结果放入模态框。因此表单类必须：

- 可以由 Laravel 容器解析；
- 提供静态或可调用的 `make($payload)`；
- 返回实现 `Illuminate\Contracts\Support\Renderable` 或提供 `render()` 的对象。

Dcat 的 `Dcat\Admin\Widgets\Form` 已提供 `make`，可以直接继承：

```php
namespace App\Admin\Forms;

use Dcat\Admin\Widgets\Form;

class BatchNoticeForm extends Form
{
    public function form()
    {
        $this->hidden('action')->value($this->data()->get('action'));
        $this->textarea('content', '通知内容')->required();
    }

    public function handle(array $input)
    {
        // 在这里调用业务服务处理 $input。

        return $this->response()->success('操作成功')->refresh();
    }
}
```

`$action` 是 Kit 注入表单的业务操作标识，具体取值和处理逻辑由应用定义。

## Grid 行操作

### `OpenIFrameTab`

点击行操作后，在 `woodynew/z-dcat-iframe-tab` 中打开或复用标签页；未安装该可选包、页面不在 iframe-tab 环境中或脚本执行异常时，会安全回退为普通页面跳转。

```php
use Woodynew\DcatAdminKit\Grid\RowActions\OpenIFrameTab;

$grid->actions(function (Dcat\Admin\Grid\Displayers\Actions $actions) {
    $toUrl = admin_url('orders/'.$actions->row->getKey().'/logs');

    $actions->append(new OpenIFrameTab('操作日志', [
        'toUrl' => $toUrl,
    ]));
});
```

构造函数：

```php
new OpenIFrameTab(?string $title = null, array $params = [])
```

`$params` 支持：

| 键 | 必填 | 说明 |
| --- | --- | --- |
| `toUrl` | 是 | 实际加载或跳转的完整 URL |
| `tabUrl` | 否 | 用于生成标签页唯一标识；默认取 `toUrl` 中问号前的路径 |
| `title` | 否 | 找不到菜单标题时使用的标签页标题；默认使用行操作标题 |

默认 `tabUrl` 不包含查询参数，因此同一路径的不同记录会复用同一个标签页，并把 iframe 地址更新为最新的 `toUrl`。若业务要求每条记录保留独立标签页，应显式传入不同的 `tabUrl`。

```php
$actions->append(new OpenIFrameTab('订单详情', [
    'toUrl' => admin_url('orders/'.$id.'?from=grid'),
    'tabUrl' => admin_url('orders/'.$id),
    'title' => '订单 #'.$id,
]));
```

### `GridModalRowAction`

在当前行打开一个大尺寸模态框表单，并把行级参数传入表单。

```php
use App\Admin\Forms\RechargeForm;
use Woodynew\DcatAdminKit\Grid\RowActions\GridModalRowAction;

$grid->actions(function (Dcat\Admin\Grid\Displayers\Actions $actions) {
    $actions->append(GridModalRowAction::make(
        '账户充值',
        20,
        RechargeForm::class,
        ['id' => $actions->row->getKey()]
    ));
});
```

构造函数：

```php
GridModalRowAction::make(
    ?string $title = null,
    $action = 1,
    ?string $formClass = null,
    array $params = []
)
```

最终表单参数为 `$params` 加上 Kit 写入的 `action`。表单类契约与 `GridFormTool` 相同。

使用懒加载表单时，实现 `LazyRenderable` 并使用 `LazyWidget`，Kit 会自动调用 `payload()`：

```php
namespace App\Admin\Forms;

use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Traits\LazyWidget;
use Dcat\Admin\Widgets\Form;

class RechargeForm extends Form implements LazyRenderable
{
    use LazyWidget;

    public function form()
    {
        $this->hidden('action')->value($this->payload['action']);
        $this->hidden('id')->value($this->payload['id']);
        $this->decimal('amount', '充值金额')->required();
    }

    public function handle(array $input)
    {
        // 校验权限并调用业务服务处理充值。

        return $this->response()->success('充值成功')->refresh();
    }
}
```

模态框只是交互入口。服务端仍需独立完成授权、参数验证和业务幂等控制。
