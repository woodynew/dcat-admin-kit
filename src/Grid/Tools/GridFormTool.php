<?php

namespace Woodynew\DcatAdminKit\Grid\Tools;

use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Grid\Tools\AbstractTool;
use Dcat\Admin\Widgets\Modal;
use Illuminate\Contracts\Support\Renderable;
use InvalidArgumentException;

class GridFormTool extends AbstractTool
{
    protected $style = 'btn btn-primary waves-effect';

    protected $data = [];

    protected $formClass;

    public function __construct($title = null, $action = 1, $formClass = null)
    {
        $this->formClass = $formClass;
        $this->data['action'] = $action;
        $this->allowHandler = false;

        parent::__construct($title);
    }

    public function title()
    {
        return $this->title ?: trans('woodynew.dcat-admin-kit::kit.form_tool');
    }

    public function confirm()
    {
        return '';
    }

    public function html()
    {
        $form = $this->makeForm($this->data);

        return Modal::make()
            ->lg()
            ->title($this->title())
            ->body($form)
            ->button(parent::html());
    }

    public function handle($request)
    {
        return null;
    }

    public function parameters()
    {
        return [];
    }

    protected function makeForm(array $payload)
    {
        if (! is_string($this->formClass) || ! class_exists($this->formClass)) {
            throw new InvalidArgumentException('GridFormTool requires an existing form class.');
        }

        $factory = app($this->formClass);

        if (! method_exists($factory, 'make')) {
            throw new InvalidArgumentException("Form class [{$this->formClass}] must provide a make method.");
        }

        $form = $factory->make($payload);

        if ($form instanceof LazyRenderable && method_exists($form, 'payload')) {
            $form->payload($payload);
        }

        if (! $form instanceof Renderable && ! method_exists($form, 'render')) {
            throw new InvalidArgumentException("Form class [{$this->formClass}] must create a renderable form.");
        }

        return $form;
    }
}
