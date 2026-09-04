# 全局特性

全局特性用于统一改变所有 Dcat Admin 页面。它们全部默认关闭，应用应逐项评估后开启。

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

修改配置后，如应用启用了配置缓存，应重新生成缓存：

```bash
php artisan config:cache
```

## 开关说明

| 配置项 | 影响范围 | 开启后的行为 |
| --- | --- | --- |
| `grid_defaults` | 所有 Grid | 隐藏删除按钮、批量删除、查看按钮和行选择器，并关闭工具按钮的 outline 样式 |
| `form_defaults` | 所有 Form | 隐藏删除、查看、返回列表按钮，以及“继续查看”“继续编辑”选项 |
| `show_defaults` | 所有 Show | 隐藏删除和编辑按钮 |
| `right_side_filter` | 所有 Grid Filter | 默认收起筛选器，并改用右侧滑出式筛选视图 |
| `top_form_tools` | 所有 Form | 在表单顶部追加“返回”和“提交”工具 |
| `back_to_top` | 所有后台页面 | 页面滚动超过约半屏后显示回到顶部按钮 |
| `grid_assets` | 所有 Grid | 增加固定表头样式、表格尺寸调整和本地 NiceScroll 横向滚动条 |
| `global_styles` | 整个后台 | 加载 Kit 的全局布局样式，包括 208px 侧栏、导航、内容区、表格、隐藏页脚和 iframe-tab 细节 |

## 按需开启

例如，只统一 Grid 操作按钮和表格滚动体验：

```php
return [
    'features' => [
        'grid_defaults' => true,
        'form_defaults' => false,
        'show_defaults' => false,
        'right_side_filter' => false,
        'top_form_tools' => false,
        'back_to_top' => false,
        'grid_assets' => true,
        'global_styles' => false,
    ],
];
```

## 使用建议

### 默认行为类开关

`grid_defaults`、`form_defaults`、`show_defaults` 会直接隐藏原生操作入口。开启前应确认业务没有依赖这些入口；权限控制仍应由应用自身完成，不能把“隐藏按钮”当作权限校验。

### 表单顶部工具

`top_form_tools` 会向每个表单追加 `TopGoBack` 和 `TopSubmit`。如果只希望少数表单使用，请保持该开关关闭，并在具体表单中手动追加，参见[操作与工具](actions-and-tools.md#topgoback-与-topsubmit)。

### Grid 资源与全局样式

`grid_assets` 只处理表格相关资源；`global_styles` 则包含明显的整体布局偏好，两者互不依赖。迁移已有后台时建议先开启 `grid_assets`，再单独评估 `global_styles` 是否会与应用主题、自定义侧栏宽度或 iframe-tab 样式冲突。

所有运行时资源均来自本地发布目录，不依赖 CDN。
