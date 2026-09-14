<?php

namespace Woodynew\DcatAdminKit;

use Dcat\Admin\Admin;
use Dcat\Admin\Form;
use Dcat\Admin\Grid;
use Dcat\Admin\Grid\Column;
use Dcat\Admin\Grid\Filter;
use Dcat\Admin\Layout\Content;
use Dcat\Admin\Show;
use Woodynew\DcatAdminKit\Actions\Form\TopGoBack;
use Woodynew\DcatAdminKit\Actions\Form\TopSubmit;
use Woodynew\DcatAdminKit\Grid\Displayers\AfterLimit;
use Woodynew\DcatAdminKit\Grid\Displayers\CopyQrCodeLink;
use Woodynew\DcatAdminKit\Grid\Displayers\MultiRow;
use Woodynew\DcatAdminKit\Grid\Displayers\TextAlert;

class Bootstrapper
{
    public function boot()
    {
        $context = Admin::context();
        $bootedKey = 'woodynew.dcat-admin-kit.bootstrapper.booted';

        if ($context->get($bootedKey)) {
            return;
        }

        $context->set($bootedKey, true);

        $this->registerContentDefaults();
        $this->registerGridDefaults();
        $this->registerFilterDefaults();
        $this->registerShowDefaults();
        $this->registerFormDefaults();
        $this->registerGlobalAssets();
        $this->registerBackToTop();
        $this->registerLocaleSwitcher();
    }

    public function registerColumnDisplayers()
    {
        Column::extend('copyqrcodelink', CopyQrCodeLink::class);
        Column::extend('multirow', MultiRow::class);
        Column::extend('textalert', TextAlert::class);
        Column::extend('afterlimit', AfterLimit::class);
    }

    protected function registerContentDefaults()
    {
        if (! $this->enabled('grid_assets')) {
            return;
        }

        Content::resolving(function () {
            Admin::script("$('.nicescroll-rails-vr, .nicescroll-rails-hr').remove();");
        });
    }

    protected function registerGridDefaults()
    {
        if (! $this->enabled('grid_defaults') && ! $this->enabled('grid_assets')) {
            return;
        }

        $gridDefaults = $this->enabled('grid_defaults');
        $gridAssets = $this->enabled('grid_assets');

        Grid::resolving(function (Grid $grid) use ($gridDefaults, $gridAssets) {
            if ($gridDefaults) {
                $grid->disableDeleteButton();
                $grid->disableBatchDelete();
                $grid->disableViewButton();
                $grid->disableRowSelector();
                $grid->toolsWithOutline(false);
            }

            if ($gridAssets) {
                $grid->addTableClass('table-head-fixed');
                Admin::js('@woodynew.dcat-admin-kit.path/js/jquery.nicescroll.min.js');
                Admin::js('@woodynew.dcat-admin-kit.path/js/grid.js');
                Admin::css('@woodynew.dcat-admin-kit.path/css/grid.css');
                Admin::script(<<<'JS'
var $gridTable = $('#grid-table');
if ($gridTable.length && $.fn.niceScroll) {
    $gridTable.parent('.table-main').niceScroll({
        cursorborder: '',
        cursoropacitymin: 1,
        cursorcolor: '#CACACA',
        cursorwidth: '10px',
        background: '#ECECF1'
    });
}
JS
                );
            }
        });
    }

    protected function registerFilterDefaults()
    {
        if (! $this->enabled('right_side_filter')) {
            return;
        }

        Filter::resolving(function (Filter $filter) {
            $filter->expand(false);
            $filter->view('woodynew.dcat-admin-kit::filter.right-side-container');
        });
    }

    protected function registerShowDefaults()
    {
        if (! $this->enabled('show_defaults')) {
            return;
        }

        Show::resolving(function (Show $show) {
            $show->disableDeleteButton();
            $show->disableEditButton();
        });
    }

    protected function registerFormDefaults()
    {
        $formDefaults = $this->enabled('form_defaults');
        $topFormTools = $this->enabled('top_form_tools');

        if (! $formDefaults && ! $topFormTools) {
            return;
        }

        Form::resolving(function (Form $form) use ($formDefaults, $topFormTools) {
            if ($topFormTools) {
                $form->tools(function (Form\Tools $tools) {
                    $tools->append(new TopGoBack());
                    $tools->append(new TopSubmit());
                });
            }

            if ($formDefaults) {
                $form->disableDeleteButton();
                $form->disableViewButton();
                $form->disableViewCheck();
                $form->disableEditingCheck();
                $form->disableListButton();
            }
        });
    }

    protected function registerGlobalAssets()
    {
        if ($this->enabled('global_styles')) {
            Admin::css('@woodynew.dcat-admin-kit.path/css/global.css');
        }
    }

    protected function registerBackToTop()
    {
        if (! $this->enabled('back_to_top')) {
            return;
        }

        admin_inject_section(
            Admin::SECTION['APP_INNER_BEFORE'],
            view('woodynew.dcat-admin-kit::partials.backtop')
        );
    }

    protected function enabled($feature)
    {
        return (bool) config("dcat-admin-kit.features.{$feature}", false);
    }

    protected function registerLocaleSwitcher()
    {
        if (! $this->enabled('locale_switcher')) {
            return;
        }

        \Dcat\Admin\Layout\Navbar::resolving(function ($navbar) {
            $navbar->right(function () {
                return view('woodynew.dcat-admin-kit::partials.locale-switcher', [
                    'locales' => config('dcat-admin-kit.locale.locales', []),
                    'locale' => app()->getLocale(),
                ])->render();
            });
        });
    }
}
