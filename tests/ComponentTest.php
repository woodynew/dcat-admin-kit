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
