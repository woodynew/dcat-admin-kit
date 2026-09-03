<?php

namespace Woodynew\DcatAdminKit\Grid\Actions;

use Dcat\Admin\Grid\Displayers\Actions;

class TextActions extends Actions
{
    protected function getViewLabel()
    {
        return '<span class="text-success">'.trans('admin.show').'</span> &nbsp;';
    }

    protected function getEditLabel()
    {
        return '<span class="text-primary">'.trans('admin.edit').'</span> &nbsp;';
    }

    protected function getQuickEditLabel()
    {
        return '<span class="text-blue-darker" title="'.e(trans('admin.quick_edit')).'">'.trans('admin.edit').'</span> &nbsp;';
    }

    protected function getDeleteLabel()
    {
        return '<span class="text-danger">'.trans('admin.delete').'</span> &nbsp;';
    }
}
