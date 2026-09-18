<?php

namespace Woodynew\DcatAdminKit\Actions\Form;

use Dcat\Admin\Form\AbstractTool;

class TopGoBack extends AbstractTool
{
    public function __construct($title = null)
    {
        parent::__construct($title ?: '<i class="feather icon-chevron-left"></i>&nbsp;'.trans('woodynew.dcat-admin-kit::kit.go_back'));
    }

    protected $style = 'btn btn-sm btn-primary';

    protected function script()
    {
        $selector = json_encode($this->selector(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

        return <<<JS
$('body')
    .off('click.dcatAdminKitTopGoBack', {$selector})
    .on('click.dcatAdminKitTopGoBack', {$selector}, function (event) {
        event.preventDefault();

        var button = $(this);

        if (button.attr('data-loading')) {
            return;
        }

        try {
            var parentWindow = window.parent;
            var iframeTabParent = parentWindow && parentWindow.iframeTabParent;
            var activeIframe = parentWindow && parentWindow.document
                ? parentWindow.document.querySelector('#iframe-tabContent .tab-pane.active iframe')
                : null;

            if (iframeTabParent && activeIframe && activeIframe.src) {
                button.buttonLoading();

                window.location.href = activeIframe.src;
                return;
            }
        } catch (error) {
            // Cross-origin parents cannot be inspected; use browser history instead.
        }

        // 没有可返回的历史记录时 history.back() 不会跳转，不进入 loading 以免按钮一直转圈。
        if (window.history.length < 2) {
            return;
        }

        button.buttonLoading();

        window.history.back();
    });

// 从 bfcache 恢复页面不会重新执行脚本，加载中的按钮需要复位。
$(window)
    .off('pageshow.dcatAdminKitTopGoBack')
    .on('pageshow.dcatAdminKitTopGoBack', function () {
        $({$selector}).buttonLoading(false);
    });
JS;
    }

    protected function html()
    {
        return '<div class="btn-group pull-right" style="margin-right: 5px">'.parent::html().'</div>';
    }

}
