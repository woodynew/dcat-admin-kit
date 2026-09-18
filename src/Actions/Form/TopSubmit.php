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

        if (button.attr('data-loading')) {
            return;
        }

        var form = button.closest('form');
        var submit = form.find('button.submit').first();

        if (!submit.length) {
            submit = button.closest('.card, .box').find('button.submit').first();
        }

        if (submit.length) {
            submit.trigger('click');
        }
    });

(function () {
    var button = $({$selector});
    var submit = button.closest('form').find('button.submit').first();

    if (!submit.length) {
        submit = button.closest('.card, .box').find('button.submit').first();
    }

    if (!submit.length || !window.MutationObserver || submit.data('dcatAdminKitTopSubmit')) {
        return;
    }

    submit.data('dcatAdminKitTopSubmit', 1);

    // 顶部按钮与表单底部原生 button.submit 共用一份 loading 状态：Dcat 在请求开始时
    // 给原生按钮加 data-loading，请求结束或返回校验错误时移除。跟随这个属性，
    // 顶部按钮只在真正发出请求时进入 loading，客户端校验未通过时保持原样。
    new MutationObserver(function () {
        if (submit.attr('data-loading')) {
            button.buttonLoading();
        } else {
            button.buttonLoading(false);
        }
    }).observe(submit[0], {attributes: true, attributeFilter: ['data-loading']});

    // 从 bfcache 恢复页面不会重新执行脚本，加载中的按钮需要复位。
    $(window).off('pageshow.dcatAdminKitTopSubmit').on('pageshow.dcatAdminKitTopSubmit', function () {
        button.buttonLoading(false);
    });
})();
JS;
    }

    protected function html()
    {
        return '<div class="btn-group pull-right" style="margin-right: 5px">'.parent::html().'</div>';
    }

}
