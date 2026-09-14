<?php

namespace Woodynew\DcatAdminKit\Actions\Form;

use Dcat\Admin\Form\AbstractTool;

class TopSubmit extends AbstractTool
{
    public function __construct($title = null)
    {
        parent::__construct($title ?: '<i class="feather icon-save"></i>&nbsp;'.trans('woodynew.dcat-admin-kit::kit.submit'));
    }

    protected $style = 'btn btn-sm btn-primary';

    protected function script()
    {
        $selector = json_encode($this->selector(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

        return <<<JS
$('body')
    .off('click.dcatAdminKitTopSubmit', {$selector})
    .on('click.dcatAdminKitTopSubmit', {$selector}, function (event) {
        event.preventDefault();

        var button = $(this);
        var form = button.closest('form');
        var submit = form.find('button.submit').first();

        if (!submit.length) {
            submit = button.closest('.card, .box').find('button.submit').first();
        }

        if (submit.length) {
            submit.trigger('click');
        }
    });
JS;
    }

    protected function html()
    {
        return '<div class="btn-group pull-right" style="margin-right: 5px">'.parent::html().'</div>';
    }

}
