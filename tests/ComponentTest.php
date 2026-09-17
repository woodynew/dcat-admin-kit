<?php

namespace Woodynew\DcatAdminKit\Tests;

use Dcat\Admin\Grid;
use Dcat\Admin\Grid\Column;
use Illuminate\Support\Fluent;
use InvalidArgumentException;
use Mockery;
use Woodynew\DcatAdminKit\Grid\Displayers\AfterLimit;
use Woodynew\DcatAdminKit\Grid\Displayers\CopyQrCodeLink;
use Woodynew\DcatAdminKit\Grid\Displayers\MultiRow;
use Woodynew\DcatAdminKit\Grid\Displayers\TextAlert;
use Woodynew\DcatAdminKit\Grid\RowActions\OpenIFrameTab;
use Woodynew\DcatAdminKit\Grid\Tools\GridFormTool;
use Woodynew\DcatAdminKit\Widgets\PostTable;

class ComponentTest extends TestCase
{
    public function test_after_limit_handles_null_short_and_escaped_long_values(): void
    {
        $this->assertSame('', $this->displayer(AfterLimit::class, null)->display());
        $this->assertSame('short', $this->displayer(AfterLimit::class, 'short')->display(10));

        $html = $this->displayer(AfterLimit::class, '<script>alert(1)</script>')->display(10);

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringContainsString('dcat-admin-kit-limit-', $html);
    }

    public function test_copy_qrcode_link_escapes_attributes_and_display_text(): void
    {
        $value = '"><script>alert(1)</script>';
        $html = $this->displayer(CopyQrCodeLink::class, $value)->display();

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;script&gt;', $html);
        $this->assertStringContainsString('dcat-admin-kit-copyable', $html);
    }

    public function test_qrcode_trigger_exposes_localized_dialog_label(): void
    {
        $this->app->make(\Woodynew\DcatAdminKit\DcatAdminKitServiceProvider::class)->init();
        foreach (['zh_CN' => '二维码', 'en' => 'QR code'] as $locale => $label) {
            $this->app->setLocale($locale);
            $html = $this->displayer(CopyQrCodeLink::class, 'https://example.com')->display();

            $this->assertStringContainsString('data-original-label="'.$label.'"', $html);
            $this->assertStringContainsString('aria-label="'.$label.'"', $html);
            $this->assertStringContainsString('aria-haspopup="dialog"', $html);
            $this->assertStringContainsString('aria-expanded="false"', $html);
            $this->assertStringNotContainsString('data-toggle="popover"', $html);
            // 弹层没有关闭按钮，入口不再输出关闭提示。
            $this->assertStringNotContainsString('data-close-label', $html);
        }
        $this->assertSame('', $this->displayer(CopyQrCodeLink::class, null)->display());
    }
    public function test_qrcode_quiet_option_defaults_and_clamps(): void
    {
        // 默认 2 个模块（与草料二维码默认码边距一致）。
        $default = $this->displayer(CopyQrCodeLink::class, 'https://example.com')->display();
        $this->assertStringContainsString('data-quiet="2"', $default);
        $this->assertStringContainsString('data-width="200"', $default);
        $this->assertStringContainsString('data-height="200"', $default);

        // 可调：打印等保守场景调到规范的 4。
        $wide = $this->displayer(CopyQrCodeLink::class, 'https://example.com')->display(null, null, 300, 300, 4);
        $this->assertStringContainsString('data-quiet="4"', $wide);
        $this->assertStringContainsString('data-width="300"', $wide);

        // 负数是非法的，收敛到 0。
        $clamped = $this->displayer(CopyQrCodeLink::class, 'https://example.com')->display(null, null, 200, 200, -3);
        $this->assertStringContainsString('data-quiet="0"', $clamped);

        // 非数字内容同样收敛到 0，不能把注入内容原样写进属性。
        $injected = $this->displayer(CopyQrCodeLink::class, 'https://example.com')->display(null, null, 200, 200, '"><script>alert(1)</script>');
        $this->assertStringNotContainsString('<script>', $injected);
        $this->assertStringContainsString('data-quiet="0"', $injected);
    }

    public function test_multi_row_separates_and_escapes_values(): void
    {
        $row = new Fluent([
            'id' => 1,
            'first' => '<b>one</b>',
            'second' => 'two',
        ]);
        $html = $this->displayer(MultiRow::class, null, $row)->display(['first', 'second']);

        $this->assertStringContainsString('&lt;b&gt;one&lt;/b&gt;', $html);
        $this->assertStringContainsString('<br>', $html);
    }

    public function test_text_alert_encodes_script_payload(): void
    {
        $html = $this->displayer(TextAlert::class, '</script><script>alert(1)</script>')->display();

        $this->assertStringNotContainsString('<script>', $html);
        $this->assertStringContainsString('&lt;/script&gt;', $html);
    }

    public function test_post_table_uses_unique_ids(): void
    {
        $first = new PostTable([], []);
        $second = new PostTable([], []);

        $this->assertNotSame($first->style(), $second->style());
    }

    public function test_grid_form_tool_rejects_missing_form_class(): void
    {
        $tool = new GridFormTool('Form', 1, 'MissingFormClass');

        $this->expectException(InvalidArgumentException::class);

        $tool->html();
    }

    public function test_iframe_action_requires_a_target_url(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OpenIFrameTab('Details', []);
    }

    public function test_iframe_action_contains_normal_navigation_fallback(): void
    {
        $action = new OpenIFrameTab('Details', ['toUrl' => '/admin/details?id=1']);
        $method = new \ReflectionMethod($action, 'script');
        $method->setAccessible(true);
        $script = $method->invoke($action);

        $this->assertStringContainsString('window.location.href = toUrl', $script);
        $this->assertStringContainsString('iframeTabParent', $script);
        $this->assertStringContainsString('replace(/[\\n  ]/g', $script);
    }

    protected function displayer($class, $value, ?Fluent $row = null)
    {
        $row = $row ?: new Fluent(['id' => 1, 'content' => $value]);
        $grid = Mockery::mock(Grid::class);
        $column = Mockery::mock(Column::class);

        $grid->shouldReceive('getKeyName')->andReturn('id');
        $column->shouldReceive('getName')->andReturn('content');
        $column->shouldReceive('getOriginal')->andReturn($value);
        $column->shouldReceive('getLabel')->andReturn('Content');

        return new $class($value, $grid, $column, $row);
    }
}
