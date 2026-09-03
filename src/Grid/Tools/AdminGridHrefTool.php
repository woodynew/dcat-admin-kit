<?php

namespace Woodynew\DcatAdminKit\Grid\Tools;

use Dcat\Admin\Grid\Tools\AbstractTool;

class AdminGridHrefTool extends AbstractTool
{
    protected $title = '跳转工具';

    protected $style = 'btn btn-primary waves-effect';

    private $href;

    public function __construct($title = null, $href = '')
    {
        $this->href = (string) $href;

        parent::__construct($title);
    }

    protected function href()
    {
        return $this->href;
    }

}
