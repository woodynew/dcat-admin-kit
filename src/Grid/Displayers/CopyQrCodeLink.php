<?php

namespace Woodynew\DcatAdminKit\Grid\Displayers;

use Dcat\Admin\Admin;
use Dcat\Admin\Grid\Displayers\AbstractDisplayer;

class CopyQrCodeLink extends AbstractDisplayer
{
    protected static $js = [
        '@qrcode',
    ];

    public function display($formatter = null, $display = null, $width = 200, $height = 200)
    {
        if ($formatter instanceof \Closure) {
            $origin = $formatter->call($this->row, $this->column->getOriginal());
        } elseif (is_string($formatter)) {
            $origin = $formatter;
        } else {
            $origin = $this->column->getOriginal();
        }

        if ($origin === null || $origin === '') {
            return '';
        }

        $origin = (string) $origin;
        $content = $display instanceof \Closure
            ? $display->call($this->row, $this->column->getOriginal())
            : $origin;
        $content = is_scalar($content) || $content === null
            ? (string) $content
            : json_encode($content, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);

        if (mb_strlen($content, 'UTF-8') > 20) {
            $content = '...'.mb_substr($content, -20, 20, 'UTF-8');
        }

        $width = max(1, (int) $width);
        $height = max(1, (int) $height);
        $originHtml = e($origin);
        $contentHtml = e($content);
        $copyTitle = e(trans('woodynew.dcat-admin-kit::kit.copy'));
        $qrCodeTitle = e(trans('woodynew.dcat-admin-kit::kit.qr_code'));

        $this->registerScript();

        return <<<HTML
<a href="javascript:void(0);"
   class="dcat-admin-kit-qrcode text-muted"
   data-text="{$originHtml}"
   data-width="{$width}"
   data-height="{$height}"
   data-original-label="{$qrCodeTitle}"
   data-html="true"
   data-toggle="popover"
   aria-label="{$qrCodeTitle}"
   tabindex="0"><i class="fa fa-qrcode"></i> {$qrCodeTitle}</a>&nbsp;
<a href="javascript:void(0);"
   class="dcat-admin-kit-copyable text-muted"
   data-content="{$originHtml}"
   title="{$originHtml}"
   aria-label="{$copyTitle}"
   data-placement="bottom"><i class="fa fa-copy"></i> {$contentHtml}</a>
HTML;
    }

    protected function registerScript()
    {
        Admin::script(<<<'JS'
(function () {
    var root = $('body');

    root.off('click.dcatAdminKitQrCode', '.dcat-admin-kit-qrcode')
        .on('click.dcatAdminKitQrCode', '.dcat-admin-kit-qrcode', function (event) {
            event.preventDefault();

            var target = $(this);
            target.empty().qrcode({
                text: String(target.attr('data-text') || ''),
                width: Number(target.attr('data-width')) || 200,
                height: Number(target.attr('data-height')) || 200,
                render: 'image'
            });

            var image = target.find('img').first();
            target.attr('data-content', image.prop('outerHTML') || '');
            image.remove();
            target.html('<i class="fa fa-qrcode"></i> ' + target.attr('data-original-label'));
            target.popover('show');
        });

    root.off('click.dcatAdminKitCopy', '.dcat-admin-kit-copyable')
        .on('click.dcatAdminKitCopy', '.dcat-admin-kit-copyable', function (event) {
            event.preventDefault();

            var target = $(this);
            var content = String(target.attr('data-content') || '');
            var fallback = function () {
                var input = $('<input type="text">').val(content).appendTo('body').select();
                document.execCommand('copy');
                input.remove();
            };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(content).catch(fallback);
            } else {
                fallback();
            }

            target.tooltip('show');
        });
}());
JS
        );
    }
}
