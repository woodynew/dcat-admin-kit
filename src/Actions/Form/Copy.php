<?php

namespace Woodynew\DcatAdminKit\Actions\Form;

use Dcat\Admin\Form\AbstractTool;

class Copy extends AbstractTool
{
    private $url;

    public function __construct($url)
    {
        $this->url = (string) $url;

        parent::__construct('<i class="feather icon-copy"></i>&nbsp;'.trans('woodynew.dcat-admin-kit::kit.copy'));
    }

    protected function href()
    {
        return $this->url;
    }

    protected function html()
    {
        return '<div class="btn-group pull-right" style="margin-right: 5px">'.parent::html().'</div>';
    }

}
