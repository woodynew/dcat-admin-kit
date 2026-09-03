<?php

namespace Woodynew\DcatAdminKit\Grid\Displayers;

use Dcat\Admin\Grid\Displayers\AbstractDisplayer;

class MultiRow extends AbstractDisplayer
{
    public function display($column = [], $formatter = null)
    {
        $rows = [];

        foreach ((array) $column as $item) {
            $content = $formatter instanceof \Closure
                ? $formatter->call($this->row, $item)
                : null;

            if ($content === null || $content === '') {
                $content = $this->row->{$item};
            }

            if (! is_scalar($content) && $content !== null) {
                $content = json_encode($content, JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_SUBSTITUTE);
            }

            $value = (string) $content;
            $title = '';

            if (mb_strlen($value, 'UTF-8') > 30) {
                $title = $value;
                $value = mb_substr($value, 0, 30, 'UTF-8').'...';
            }

            $rows[] = sprintf(
                '<div class="d-flex" style="white-space: break-spaces"><label style="white-space: nowrap">%s:</label><label style="min-width: 200px; max-width: 300px" title="%s">%s</label></div>',
                e(admin_trans_field($item)),
                e($title),
                e($value)
            );
        }

        return implode('<br>', $rows);
    }
}
