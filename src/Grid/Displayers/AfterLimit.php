<?php

namespace Woodynew\DcatAdminKit\Grid\Displayers;

use Dcat\Admin\Admin;
use Dcat\Admin\Grid\Displayers\AbstractDisplayer;
use Dcat\Admin\Support\Helper;

class AfterLimit extends AbstractDisplayer
{
    public function display($limit = 100, $end = '...')
    {
        $limit = max(0, (int) $limit);

        if ($this->value === null) {
            return '';
        }

        if (! is_scalar($this->value)) {
            $values = Helper::array($this->value);

            if (count($values) <= $limit) {
                return $values;
            }

            $values = array_slice($values, -$limit, $limit);
            $values[] = $end;

            return $values;
        }

        $rawValue = (string) $this->value;

        if (mb_strlen($rawValue, 'UTF-8') <= $limit) {
            return Helper::htmlEntityEncode($rawValue);
        }

        $value = Helper::htmlEntityEncode($rawValue);
        $shortValue = Helper::htmlEntityEncode(rtrim(mb_substr($rawValue, -$limit, $limit, 'UTF-8')).(string) $end);
        $id = 'dcat-admin-kit-limit-'.md5($this->column->getName().$this->getKey().$rawValue);
        $selector = json_encode('#'.$id, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);

        Admin::script(<<<JS
$('body')
    .off('click.dcatAdminKitAfterLimit', {$selector} + ' .limit-more')
    .on('click.dcatAdminKitAfterLimit', {$selector} + ' .limit-more', function (event) {
        event.preventDefault();
        $(this).closest('.limit-text').toggleClass('d-none').siblings('.limit-text').toggleClass('d-none');
    });
JS
        );

        return <<<HTML
<div id="{$id}">
    <div class="limit-text">
        <span class="text">{$shortValue}</span>
        &nbsp;<a href="javascript:void(0);" class="limit-more"><i class="fa fa-angle-double-down"></i></a>
    </div>
    <div class="limit-text d-none">
        <span class="text">{$value}</span>
        &nbsp;<a href="javascript:void(0);" class="limit-more"><i class="fa fa-angle-double-up"></i></a>
    </div>
</div>
HTML;
    }
}
