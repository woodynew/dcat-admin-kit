<?php

namespace Woodynew\DcatAdminKit\Grid\Displayers;

use Dcat\Admin\Admin;
use Dcat\Admin\Grid\Displayers\AbstractDisplayer;

class CopyQrCodeLink extends AbstractDisplayer
{
    protected static $js = [
        '@qrcode',
    ];

    /**
     * @param  mixed  $formatter  闭包时用于计算二维码与复制的完整内容；字符串时作为固定内容；null 时取列原始值
     * @param  mixed  $display    闭包时用于计算页面上显示的短标签，不影响复制与二维码内容
     * @param  int  $width        二维码外框尺寸（px）
     * @param  int  $height       同上；二维码固定正方形，取两者较小值作为边长
     * @param  int  $quiet        静区宽度，单位是模块（色块）。默认 2，与草料二维码的默认码边距一致。
     *                            规范要求 4（Denso Wave / ZXing），0 会导致传统解码器无法识别；
     *                            取值过大时模块会被压到不足 1px，同样扫不出来，此时应同步放大 $width。
     */
    public function display($formatter = null, $display = null, $width = 200, $height = 200, $quiet = 2)
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
        $quiet = max(0, (int) $quiet);
        $originHtml = e($origin);
        $contentHtml = e($content);
        $copyTitle = e(trans('woodynew.dcat-admin-kit::kit.copy'));
        $qrCodeTitle = e(trans('woodynew.dcat-admin-kit::kit.qr_code'));
        $copySuccess = e(trans('woodynew.dcat-admin-kit::kit.copy_success'));
        $copyFailed = e(trans('woodynew.dcat-admin-kit::kit.copy_failed'));

        $this->registerScript();

        return <<<HTML
<a href="javascript:void(0);"
   class="dcat-admin-kit-qrcode text-muted"
   data-text="{$originHtml}"
   data-width="{$width}"
   data-height="{$height}"
   data-quiet="{$quiet}"
   data-original-label="{$qrCodeTitle}"
   aria-label="{$qrCodeTitle}"
   aria-haspopup="dialog"
   aria-expanded="false"
   tabindex="0"><i class="fa fa-qrcode"></i> {$qrCodeTitle}</a>&nbsp;
<a href="javascript:void(0);"
   class="dcat-admin-kit-copyable text-muted"
   data-content="{$originHtml}"
   title="{$originHtml}"
   aria-label="{$copyTitle}"
   data-copy-success="{$copySuccess}"
   data-copy-failed="{$copyFailed}"
   data-placement="bottom"><i class="fa fa-copy"></i> {$contentHtml}</a>
HTML;
    }

    /**
     * 二维码弹层样式。
     *
     * 弹层内容就是二维码本身。二维码必须放在白色底上：四周的白色留白（静区）是扫码的必要条件，
     * 也决定弹层在页面上的边界是否清楚。Dcat 暗色主题会把 .popover-body 刷成 #222233，
     * 深色底直接贴着二维码边缘，既让外框和页面背景几乎同色，也破坏了静区，
     * 所以这里把弹层底色固定为白色，并让箭头、阴影跟随。
     */
    protected function registerStyle()
    {
        Admin::style(<<<'CSS'
.dcat-admin-kit-qrcode-popover .popover-body {
    background-color: #fff;
    /* adminlte 默认给 .popover-body 加 7px/10.5px 内边距，会在二维码自带的静区外
       再套一圈白边，卡片比图片大出一圈。这里去掉内边距让卡片正好贴住图片。 */
    padding: 0;
    /* 卡片是圆角的，图片是方的；不留内边距时图片四角会盖住圆角。 */
    border-radius: inherit;
    overflow: hidden;
}

body.dark-mode .popover.dcat-admin-kit-qrcode-popover .popover-body {
    background-color: #fff;
}

/* 箭头是卡片的一部分，留在主题深色会变成压在二维码边上的深色三角。 */
body.dark-mode .popover.dcat-admin-kit-qrcode-popover .arrow::before {
    border-color: transparent;
}

body.dark-mode .popover.dcat-admin-kit-qrcode-popover[x-placement^="top"] .arrow::after,
body.dark-mode .popover.dcat-admin-kit-qrcode-popover.bs-popover-top .arrow::after {
    border-top-color: #fff;
}

body.dark-mode .popover.dcat-admin-kit-qrcode-popover[x-placement^="bottom"] .arrow::after,
body.dark-mode .popover.dcat-admin-kit-qrcode-popover.bs-popover-bottom .arrow::after {
    border-bottom-color: #fff;
}

body.dark-mode .popover.dcat-admin-kit-qrcode-popover[x-placement^="left"] .arrow::after,
body.dark-mode .popover.dcat-admin-kit-qrcode-popover.bs-popover-left .arrow::after {
    border-left-color: #fff;
}

body.dark-mode .popover.dcat-admin-kit-qrcode-popover[x-placement^="right"] .arrow::after,
body.dark-mode .popover.dcat-admin-kit-qrcode-popover.bs-popover-right .arrow::after {
    border-right-color: #fff;
}

/* 浅色阴影在深色页面上不可见，换成深色投影让白色卡片浮起来。 */
body.dark-mode .popover.dcat-admin-kit-qrcode-popover {
    box-shadow: 0 10px 30px rgba(0, 0, 0, .55);
}

/* 复制结果提示：toastr 默认 300px 宽、大内边距，对"已复制"这种短提示太占地方，
   这里收窄成小号提示。只作用于本组件的 toast，不影响后台其他 toast。 */
#toast-container > .dcat-admin-kit-toast {
    width: auto;
    max-width: 240px;
    padding: 8px 12px 8px 38px;
    font-size: 13px;
    background-position: 12px center;
    background-size: 18px;
    border-radius: .25rem;
}
CSS);
    }

    protected function registerScript()
    {
        $this->registerStyle();

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

            // 属性缺失或非法时用默认值；显式传 0 要保留，不能被当成"空值"替换掉。
            var quietAttr = Number(target.attr('data-quiet'));

            var code = $('<div>').qrcode({
                text: String(target.attr('data-text') || ''),
                // jquery-qrcode 只认 size，且二维码必须是正方形；取宽高中较小值，
                // 保证渲染结果不会超出调用方要求的尺寸。原来传的 width/height 会被插件忽略。
                size: Math.min(
                    Number(target.attr('data-width')) || 200,
                    Number(target.attr('data-height')) || 200
                ),
                // 静区宽度由调用方决定，默认 2 个模块（与草料二维码默认码边距一致）。
                quiet: isNaN(quietAttr) ? 2 : Math.max(0, quietAttr),
                render: 'image'
            });

            var label = target.attr('data-original-label');
            var image = code.find('img').first().detach().attr('alt', label);

            if (target.data('bs.popover')) target.popover('dispose');
            target.popover({
                trigger: 'manual',
                html: true,
                animation: false,
                container: 'body',
                // 弹层只有二维码本身：不带标题栏，也不带关闭按钮。
                template: '<div class="popover dcat-admin-kit-qrcode-popover" role="dialog"><div class="arrow"></div><div class="popover-body"></div></div>',
                content: image
            }).popover('show');
            root.data(activeKey, target);
            target.attr('aria-expanded', 'true');
            var tip = document.getElementById(target.attr('aria-describedby'));
            if (tip) {
                target.attr('aria-controls', tip.id);
                $(tip).attr('aria-label', label);
            }
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
                var ok = document.execCommand('copy');
                input.remove();
                notify(target, ok);
            };

            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(content).then(function () {
                    notify(target, true);
                })['catch'](fallback);
            } else {
                fallback();
            }
        });

    // 复制结果用短 toast 反馈；加载不到 toastr 时静默跳过，不影响复制本身。
    function notify(target, success) {
        var message = target.attr(success ? 'data-copy-success' : 'data-copy-failed');
        if (!message) return;

        var options = {timeOut: 1500, toastClass: 'toast dcat-admin-kit-toast'};

        if (window.toastr) {
            toastr[success ? 'success' : 'error'](message, '', options);
        } else if (window.Dcat && typeof Dcat[success ? 'success' : 'error'] === 'function') {
            Dcat[success ? 'success' : 'error'](message, '', options);
        }
    }

}());
JS
        );
    }
}
