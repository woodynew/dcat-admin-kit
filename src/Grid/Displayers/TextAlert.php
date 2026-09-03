<?php

namespace Woodynew\DcatAdminKit\Grid\Displayers;

use Dcat\Admin\Admin;
use Dcat\Admin\Grid\Displayers\AbstractDisplayer;

class TextAlert extends AbstractDisplayer
{
    public function display($limit = 15, $before = 1)
    {
        $origin = $this->column->getOriginal();

        if ($origin === null || $origin === '') {
            return '';
        }

        $origin = is_scalar($origin)
            ? (string) $origin
            : json_encode($origin, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
        $limit = max(0, (int) $limit);
        $value = $origin;

        if (mb_strlen($value, 'UTF-8') > $limit) {
            $value = $before
                ? mb_substr($value, 0, $limit, 'UTF-8')
                : mb_substr($value, -$limit, $limit, 'UTF-8');
        }

        $id = 'dcat-admin-kit-alert-'.md5($this->column->getName().$this->getKey().$origin);
        $selector = json_encode('#'.$id, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
        $jsonFlags = JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT | JSON_INVALID_UTF8_SUBSTITUTE;
        $title = json_encode((string) $this->column->getLabel(), $jsonFlags);
        $content = json_encode(str_replace(["\r\n", "\r", "\n"], ' ', $origin), $jsonFlags);

        Admin::requireAssets('@layer');
        Admin::script(<<<JS
$('body')
    .off('click.dcatAdminKitTextAlert', {$selector})
    .on('click.dcatAdminKitTextAlert', {$selector}, function (event) {
        event.preventDefault();
        layer.open({title: {$title}, content: {$content}});
    });
JS
        );

        return '<a id="'.e($id).'" href="javascript:void(0)" title="'.e(trans('woodynew.dcat-admin-kit::kit.view_text')).'">'.e($value).'</a>';
    }
}
