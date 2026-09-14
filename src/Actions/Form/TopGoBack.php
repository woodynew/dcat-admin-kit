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

        try {
            var parentWindow = window.parent;
            var iframeTabParent = parentWindow && parentWindow.iframeTabParent;
            var activeIframe = parentWindow && parentWindow.document
                ? parentWindow.document.querySelector('#iframe-tabContent .tab-pane.active iframe')
                : null;

            if (iframeTabParent && activeIframe && activeIframe.src) {
                window.location.href = activeIframe.src;
                return;
            }
        } catch (error) {
            // Cross-origin parents cannot be inspected; use browser history instead.
        }

        window.history.back();
    });
JS;
    }

    protected function html()
    {
        return '<div class="btn-group pull-right" style="margin-right: 5px">'.parent::html().'</div>';
    }

}
