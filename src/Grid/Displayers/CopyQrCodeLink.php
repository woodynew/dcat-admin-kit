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
        $closeTitle = e(trans('woodynew.dcat-admin-kit::kit.close'));

        $this->registerScript();

        return <<<HTML
<a href="javascript:void(0);"
   class="dcat-admin-kit-qrcode text-muted"
   data-text="{$originHtml}"
   data-width="{$width}"
   data-height="{$height}"
   data-original-label="{$qrCodeTitle}"
   data-close-label="{$closeTitle}"
   aria-label="{$qrCodeTitle}"
   aria-haspopup="dialog"
   aria-expanded="false"
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
    var activeKey = 'dcatAdminKitQrCodeActive';

    function closeQrCode(restoreFocus) {
        var active = root.data(activeKey);
        if (!active) return;

        root.removeData(activeKey);
        active.popover('dispose');
        active.attr('aria-expanded', 'false').removeAttr('aria-describedby aria-controls');
        if (restoreFocus && document.documentElement.contains(active[0])) {
            active.trigger('focus');
        }
    }

    // Re-registering after a partial render must not leave an old Popper instance.
    closeQrCode(false);

    root.off('click.dcatAdminKitQrCode', '.dcat-admin-kit-qrcode')
        .on('click.dcatAdminKitQrCode', '.dcat-admin-kit-qrcode', function (event) {
            event.preventDefault();

            var target = $(this);
            var active = root.data(activeKey);
            if (active && active[0] === this) {
                closeQrCode(false);
                return;
            }
            closeQrCode(false);

            var code = $('<div>').qrcode({
                text: String(target.attr('data-text') || ''),
                width: Number(target.attr('data-width')) || 200,
                height: Number(target.attr('data-height')) || 200,
                render: 'image'
            });

            var label = target.attr('data-original-label');
            var image = code.find('img').first().detach().attr('alt', label);
            var header = $('<div>').append($('<span>').text(label)).append(
                $('<button>', {
                    type: 'button',
                    class: 'close dcat-admin-kit-qrcode-close',
                    'aria-label': target.attr('data-close-label')
                }).css({marginLeft: '12px', color: 'inherit', opacity: 1}).append($('<span aria-hidden="true">').text('×'))
            );

            if (target.data('bs.popover')) target.popover('dispose');
            target.popover({
                trigger: 'manual',
                html: true,
                animation: false,
                container: 'body',
                template: '<div class="popover dcat-admin-kit-qrcode-popover" role="dialog"><div class="arrow"></div><div class="popover-header"></div><div class="popover-body"></div></div>',
                title: header,
                content: image
            }).popover('show');
            root.data(activeKey, target);
            target.attr('aria-expanded', 'true');
            var tip = document.getElementById(target.attr('aria-describedby'));
            if (tip) {
                target.attr('aria-controls', tip.id);
                // Dcat sets popover header text to white without a matching background.
                // Scope a contrasting pair to this QR header, independent of the host theme.
                $(tip).attr('aria-label', label).find('.popover-header').css({backgroundColor: '#f1f3f5', color: '#343a40'});
                // The tip still sits at the document origin until Popper applies its
                // transform, so a scrolling focus would drag the page to the top.
                var closeButton = $(tip).find('.dcat-admin-kit-qrcode-close').get(0);
                if (closeButton) closeButton.focus({preventScroll: true});
            }
        });

    root.off('click.dcatAdminKitQrCodeClose', '.dcat-admin-kit-qrcode-close')
        .on('click.dcatAdminKitQrCodeClose', '.dcat-admin-kit-qrcode-close', function (event) {
            event.preventDefault();
            closeQrCode(true);
        });

    root.off('click.dcatAdminKitQrCodeOutside').on('click.dcatAdminKitQrCodeOutside', function (event) {
        if (!$(event.target).closest('.dcat-admin-kit-qrcode, .dcat-admin-kit-qrcode-popover').length) {
            closeQrCode(false);
        }
    });

    $(document).off('keydown.dcatAdminKitQrCode').on('keydown.dcatAdminKitQrCode', function (event) {
        if ((event.key === 'Escape' || event.keyCode === 27) && root.data(activeKey)) {
            event.preventDefault();
            closeQrCode(true);
        }
    }).off('pjax:beforeReplace.dcatAdminKitQrCode').on('pjax:beforeReplace.dcatAdminKitQrCode', function () {
        closeQrCode(false);
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
