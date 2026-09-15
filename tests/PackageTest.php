<?php

namespace Woodynew\DcatAdminKit\Tests;

use Dcat\Admin\Admin;
use Dcat\Admin\Grid\Column;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Woodynew\DcatAdminKit\Actions\Form\Copy;
use Woodynew\DcatAdminKit\Actions\Form\TopGoBack;
use Woodynew\DcatAdminKit\Actions\Form\TopSubmit;
use Woodynew\DcatAdminKit\Bootstrapper;
use Woodynew\DcatAdminKit\DcatAdminKitServiceProvider;
use Woodynew\DcatAdminKit\Grid\Actions\TextActions;
use Woodynew\DcatAdminKit\Grid\Displayers\AfterLimit;
use Woodynew\DcatAdminKit\Grid\Displayers\CopyQrCodeLink;
use Woodynew\DcatAdminKit\Grid\Displayers\MultiRow;
use Woodynew\DcatAdminKit\Grid\Displayers\TextAlert;
use Woodynew\DcatAdminKit\Grid\RowActions\GridModalRowAction;
use Woodynew\DcatAdminKit\Grid\RowActions\OpenIFrameTab;
use Woodynew\DcatAdminKit\Grid\Tools\AdminGridHrefTool;
use Woodynew\DcatAdminKit\Grid\Tools\GridFormTool;
use Woodynew\DcatAdminKit\Support\AdminFormUtil;
use Woodynew\DcatAdminKit\Widgets\PostTable;

class PackageTest extends TestCase
{
    public function test_global_features_are_disabled_by_default(): void
    {
        $features = config('dcat-admin-kit.features');

        $this->assertNotEmpty($features);
        $this->assertSame([], array_filter($features));
    }

    public function test_all_public_components_are_autoloadable(): void
    {
        $classes = [
            Copy::class,
            TopGoBack::class,
            TopSubmit::class,
            TextActions::class,
            AfterLimit::class,
            CopyQrCodeLink::class,
            MultiRow::class,
            TextAlert::class,
            AdminGridHrefTool::class,
            GridFormTool::class,
            OpenIFrameTab::class,
            GridModalRowAction::class,
            PostTable::class,
            AdminFormUtil::class,
        ];

        $this->assertCount(14, $classes);

        foreach ($classes as $class) {
            $this->assertTrue(class_exists($class), $class);
        }
    }

    public function test_column_aliases_are_registered_even_when_extension_is_disabled(): void
    {
        $extensions = Column::extensions();

        $this->assertSame(CopyQrCodeLink::class, $extensions['copyqrcodelink']);
        $this->assertSame(MultiRow::class, $extensions['multirow']);
        $this->assertSame(TextAlert::class, $extensions['textalert']);
        $this->assertSame(AfterLimit::class, $extensions['afterlimit']);
    }

    public function test_provider_loads_namespaced_views_when_initialized(): void
    {
        $this->app->make(DcatAdminKitServiceProvider::class)->init();

        $this->assertTrue(view()->exists('woodynew.dcat-admin-kit::filter.right-side-container'));
        $this->assertTrue(view()->exists('woodynew.dcat-admin-kit::partials.backtop'));
    }

    public function test_standalone_tools_translate_without_enabling_the_extension(): void
    {
        $this->assertFalse(Admin::extension()->enabled('woodynew.dcat-admin-kit'));
        app()->setLocale('en');
        $this->assertStringContainsString('Back', (new TopGoBack())->title());
        $this->assertSame('Copy', trans('woodynew.dcat-admin-kit::kit.copy'));
    }

    public function test_enabling_global_features_registers_builder_listeners_once(): void
    {
        $builders = [
            \Dcat\Admin\Layout\Content::class,
            \Dcat\Admin\Grid::class,
            \Dcat\Admin\Grid\Filter::class,
            \Dcat\Admin\Show::class,
            \Dcat\Admin\Form::class,
        ];
        $before = [];

        foreach ($builders as $builder) {
            $before[$builder] = count(Admin::context()->get($builder.':builder:resolving') ?: []);
        }

        $features = array_fill_keys(array_keys(config('dcat-admin-kit.features')), true);
        $features['back_to_top'] = false;
        config(['dcat-admin-kit.features' => $features]);
        $bootstrapper = new Bootstrapper();
        $bootstrapper->boot();

        $registered = [];

        foreach ($builders as $builder) {
            $after = count(Admin::context()->get($builder.':builder:resolving') ?: []);
            $this->assertGreaterThan($before[$builder], $after, $builder);
            $registered[$builder] = $after;
        }

        $bootstrapper->boot();

        foreach ($builders as $builder) {
            $after = count(Admin::context()->get($builder.':builder:resolving') ?: []);
            $this->assertSame($registered[$builder], $after, $builder);
        }
    }

    public function test_runtime_resources_do_not_reference_remote_urls(): void
    {
        $root = dirname(__DIR__);
        $files = [];

        foreach ([$root.'/src', $root.'/resources/views', $root.'/resources/assets/css'] as $directory) {
            $iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($directory));

            foreach ($iterator as $file) {
                if ($file->isFile()) {
                    $files[] = $file->getPathname();
                }
            }
        }

        $files[] = $root.'/resources/assets/js/grid.js';
        $files[] = $root.'/resources/assets/js/jquery.nicescroll.min.js';

        foreach ($files as $file) {
            $contents = file_get_contents($file);
            $runtimeContents = preg_replace('#/\*.*?\*/#s', '', $contents);

            $this->assertDoesNotMatchRegularExpression(
                '/(?:src|href)=["\'](?:https?:)?\/\//i',
                $runtimeContents,
                $file
            );
            $this->assertDoesNotMatchRegularExpression('/url\(["\']?https?:\/\//i', $runtimeContents, $file);
        }
    }

    public function test_install_command_stops_when_dcat_tables_are_missing(): void
    {
        $this->artisan('dcat-admin-kit:install')
            ->expectsOutput('Dcat Admin table [admin_extensions] does not exist. Run php artisan admin:install after confirming the target database.')
            ->assertExitCode(1);
    }

    public function test_install_command_is_idempotent(): void
    {
        Schema::create('admin_extensions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100)->unique();
            $table->string('version', 20)->default('');
            $table->tinyInteger('is_enabled')->default(0);
            $table->text('options')->nullable();
            $table->timestamps();
        });
        Schema::create('admin_extension_histories', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 100);
            $table->tinyInteger('type')->default(1);
            $table->string('version', 20)->default('0');
            $table->text('detail')->nullable();
            $table->timestamps();
        });

        $this->assertTrue(Admin::extension()->has('woodynew/dcat-admin-kit'));

        $this->artisan('dcat-admin-kit:install')->assertExitCode(0);
        $this->artisan('dcat-admin-kit:install')->assertExitCode(0);

        $this->assertDatabaseCount('admin_extensions', 1);
        $this->assertDatabaseHas('admin_extensions', [
            'name' => 'woodynew.dcat-admin-kit',
            'version' => '0.2.2',
            'is_enabled' => 1,
        ]);
        $this->assertFileExists(public_path('vendor/dcat-admin-extensions/woodynew/dcat-admin-kit/css/grid.css'));
        $this->assertFileExists(public_path('vendor/dcat-admin-extensions/woodynew/dcat-admin-kit/js/grid.js'));
        $this->assertFileExists(public_path('vendor/dcat-admin-extensions/woodynew/dcat-admin-kit/images/gotop.svg'));
    }
}
