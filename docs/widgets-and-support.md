# 组件与辅助工具

## `PostTable`

`PostTable` 是一个实现了 `Renderable` 的响应式表格，适合在 Grid 展开行、列 `display` 回调或其他可渲染区域中展示汇总数据。

```php
use Woodynew\DcatAdminKit\Widgets\PostTable;

$grid->column('statistics', '统计')->display(function () {
    return new PostTable(
        ['日期', '订单数', '金额'],
        [
            ['2026-09-01', 12, '128.00'],
            ['2026-09-02', 18, '236.00'],
        ]
    );
});
```

构造函数：

```php
new PostTable(array $header = [], array $data = [])
```

- `$header`：表头数组；
- `$data`：二维行数据，每一行的列数和顺序应与表头一致。

每个实例都会生成独立 DOM ID，因此同一页面可以渲染多个 `PostTable`，样式不会因 ID 重复而串用。组件会添加响应式容器和 40px 单元格高度。

## `AdminFormUtil`

`AdminFormUtil::isCreatingEditing` 用于判断当前 Form 请求是否是需要执行业务字段整理的“新建或正常编辑”流程。

```php
use Woodynew\DcatAdminKit\Support\AdminFormUtil;
use Illuminate\Support\Str;

$form->saving(function (Dcat\Admin\Form $form) {
    if (! AdminFormUtil::isCreatingEditing($form)) {
        return;
    }

    $form->slug = Str::slug($form->title);
});
```

方法签名：

```php
AdminFormUtil::isCreatingEditing(
    Dcat\Admin\Form $form,
    array|string $existField = []
): bool
```

返回规则：

1. 新建表单返回 `true`；
2. 非编辑表单返回 `false`；
3. 文件删除或文件上传的局部请求包含 `_file_del_` 或 `_file_` 时返回 `false`；
4. 传入 `$existField` 时，编辑请求必须包含所有指定字段；
5. 满足以上条件的正常编辑请求返回 `true`。

例如，只在完整编辑请求同时包含 `title` 和 `status` 时处理：

```php
$form->saving(function (Dcat\Admin\Form $form) {
    if (AdminFormUtil::isCreatingEditing($form, ['title', 'status'])) {
        // 整理只属于完整表单提交的数据。
    }
});
```

该方法只负责识别请求形态，不替代 Laravel 验证、权限检查或业务状态判断。
