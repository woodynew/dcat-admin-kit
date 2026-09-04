# Grid 列显示器

Kit 会自动为 `Dcat\Admin\Grid\Column` 注册 4 个方法。它们与 Dcat Admin 内置的 `display`、`limit`、`sortable` 等列方法一样，通过链式调用使用，不需要在控制器中导入显示器类。

## `copyqrcodelink`

同时提供二维码预览和复制完整内容的入口。

```php
$grid->column('url')->copyqrcodelink();
```

完整签名：

```php
copyqrcodelink($formatter = null, $display = null, $width = 200, $height = 200)
```

| 参数 | 说明 |
| --- | --- |
| `$formatter` | `Closure` 时用于计算要生成二维码和复制的完整内容；字符串时直接作为固定内容；`null` 时使用列原始值 |
| `$display` | `Closure` 时用于计算页面上显示的短标签，不影响复制和二维码内容 |
| `$width` | 二维码宽度，默认 200，最小为 1 |
| `$height` | 二维码高度，默认 200，最小为 1 |

回调会绑定到当前行，因此可以通过 `$this` 读取其他字段：

```php
$grid->column('short_url', '短链接')->copyqrcodelink(
    function ($original) {
        return route('short-url.redirect', ['code' => $this->code]);
    },
    function ($original) {
        return $this->code;
    },
    240,
    240
);
```

页面标签超过 20 个字符时只显示末尾 20 个字符，复制和二维码仍使用完整内容。原始值为 `null` 或空字符串时不输出内容。

## `multirow`

把当前记录的多个字段合并到一个 Grid 单元格中，适合展示“名称 + 状态”“申请信息”等紧凑信息块。

```php
$grid->column('summary', '概要')->multirow(['name', 'status']);
```

完整签名：

```php
multirow(array $columns = [], ?Closure $formatter = null)
```

每个字段会渲染为“字段翻译：值”。值超过 30 个字符时显示前 30 个字符，并把完整内容放入 `title` 属性。

需要按字段定制内容时，回调参数是当前字段名，回调同样绑定到当前行：

```php
$grid->column('contact', '联系方式')->multirow(
    ['name', 'mobile'],
    function ($field) {
        if ($field === 'mobile') {
            return preg_replace('/(\d{3})\d{4}(\d{4})/', '$1****$2', $this->{$field});
        }

        return $this->{$field};
    }
);
```

回调返回 `null` 或空字符串时会回退到当前行的原字段值。字段名通过 `admin_trans_field` 翻译，建议在应用的字段翻译文件中补齐对应标签。

## `textalert`

显示一段截断文本，点击后用 Dcat Admin 的 Layer 弹窗查看完整内容。

```php
$grid->column('payload')->textalert();
$grid->column('payload')->textalert(50);
```

完整签名：

```php
textalert($limit = 15, $before = 1)
```

| 参数 | 说明 |
| --- | --- |
| `$limit` | 列中预览文本的最大字符数，默认 15 |
| `$before` | 为真时保留开头，为假时保留末尾 |

```php
// 显示末尾 30 个字符，点击查看全文
$grid->column('error_message')->textalert(30, 0);
```

弹窗标题使用列标签。完整内容中的换行会转为空格；输出会进行 HTML 和 JavaScript 上下文编码。原始值为空时不输出链接。

## `afterlimit`

长文本默认显示末尾片段，并提供展开/收起完整内容的入口。

```php
$grid->column('request_id')->afterlimit();
$grid->column('request_id')->afterlimit(12, '…');
```

完整签名：

```php
afterlimit($limit = 100, $end = '...')
```

| 参数 | 说明 |
| --- | --- |
| `$limit` | 保留的末尾字符数或数组元素数，默认 100 |
| `$end` | 截断标记，默认 `...` |

字符串长度未超过限制时直接显示；超过限制时显示末尾片段，点击向下图标展开全文。标量文本会进行 HTML 转义。

列值为数组或可转为数组的值时，组件保留最后 `$limit` 个元素并追加截断标记，由后续列渲染流程继续处理。

## 与其他列方法组合

列显示器负责输出内容，筛选和排序仍使用 Dcat Admin 原有能力：

```php
$grid->column('url')
    ->copyqrcodelink()
    ->filter();
```

如果多个显示器都要重写同一列的最终内容，应拆成不同虚拟列，避免依赖不明确的渲染顺序。
