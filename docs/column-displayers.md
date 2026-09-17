# Grid 列显示器

Kit 会自动为 `Dcat\Admin\Grid\Column` 注册 4 个方法。它们与 Dcat Admin 内置的 `display`、`limit`、`sortable` 等列方法一样，通过链式调用使用，不需要在控制器中导入显示器类。

## `copyqrcodelink`

同时提供二维码预览和复制完整内容的入口。

```php
$grid->column('url')->copyqrcodelink();
```

完整签名：

```php
copyqrcodelink($formatter = null, $display = null, $width = 200, $height = 200, $quiet = 2)
```

| 参数 | 说明 |
| --- | --- |
| `$formatter` | `Closure` 时用于计算要生成二维码和复制的完整内容；字符串时直接作为固定内容；`null` 时使用列原始值 |
| `$display` | `Closure` 时用于计算页面上显示的短标签，不影响复制和二维码内容 |
| `$width` | 二维码外框尺寸，默认 200，最小为 1 |
| `$height` | 同上；二维码固定为正方形，取两者较小值作为边长 |
| `$quiet` | 静区（白边）宽度，单位是模块，默认 2，最小为 0 |

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

二维码预留 2 个模块的静区（白边），与草料二维码的默认「码边距」一致。静区包含在 `$width`/`$height` 指定的外框尺寸内，渲染结果不会超出调用方要求的尺寸。弹层卡片紧贴图片，不额外增加内边距，看到的白边即二维码自带的静区。

关于静区宽度：Denso Wave 的规范要求四边各留 4 个模块，ZXing 参考实现同样用 4。实测 0 个模块时传统解码器（OpenCV）完全无法识别，1 个模块起即可正常识别，2 与 4 在缩放到 1/3、JPEG 压缩、贴深色底等场景下测不出差别，因此默认取 2 以收窄白边。打印、激光雕刻或预期会被大幅缩小的场景建议调到规范的 4：

```php
$grid->column('url', '链接')->copyqrcodelink(null, null, 300, 300, 4);
```

静区是按模块计算的，会随 `$width`/`$height` 等比缩放。取值过大时单个模块会被压到不足 1px，同样扫不出来，此时应同步放大外框尺寸。

页面标签超过 20 个字符时只显示末尾 20 个字符，复制和二维码仍使用完整内容。原始值为 `null` 或空字符串时不输出内容。

二维码弹层只显示二维码本身，没有标题栏和关闭按钮。支持点击弹层外部、按 `Esc` 或再次点击同一个二维码入口关闭。点击图片本身不会关闭；打开另一个二维码会关闭先前的弹层。同一页面最多显示一个二维码弹层，PJAX 替换页面内容时自动清理。按 `Esc` 关闭后，焦点回到对应入口。现有调用方式无需增加参数。

点击复制入口后，右下角会出现一条「已复制」短提示，约 1.5 秒后自动消失，不需要手动关闭；复制失败时提示失败原因，内容仍保留在页面上可手动复制。提示文案跟随后台语言。

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
