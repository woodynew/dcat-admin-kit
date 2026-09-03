<?php

namespace Woodynew\DcatAdminKit\Widgets;

use Dcat\Admin\Admin;
use Dcat\Admin\Widgets\Table;
use Illuminate\Contracts\Support\Renderable;

class PostTable implements Renderable
{
    protected $header;

    protected $data;

    protected $id;

    public function __construct($header = [], $data = [])
    {
        $this->header = $header;
        $this->data = $data;
        $this->id = str_replace('.', '', uniqid('dcat-admin-kit-post-table-', true));
    }

    public function style()
    {
        return '#'.$this->id.' tr td { height: 40px; }';
    }

    public function render()
    {
        Admin::style($this->style());

        $table = Table::make($this->header, $this->data, 'table custom-data-table data-table');
        $table->id($this->id);
        $table->class('table custom-data-table data-table');

        return '<div class="table-responsive table-wrapper table-collapse">'.$table->render().'</div>';
    }
}
