<?php

namespace Woodynew\DcatAdminKit\Grid\RowActions;

use Dcat\Admin\Contracts\LazyRenderable;
use Dcat\Admin\Grid\RowAction;
use Dcat\Admin\Widgets\Modal;
use Illuminate\Contracts\Support\Renderable;
use InvalidArgumentException;

class GridModalRowAction extends RowAction
{
    protected $title = 'GridModalRowAction';

    private $params;

    private $formClass;

    public function __construct($title = null, $action = 1, $formClass = null, $params = [])
    {
        $this->formClass = $formClass;
        $this->params = array_merge($params, ['action' => $action]);

        parent::__construct($title);
    }

    public function confirm()
    {
        return '';
    }

    public function render()
    {
        $form = $this->makeForm();

        return Modal::make()
            ->lg()
            ->title($this->title)
            ->body($form)
            ->button(parent::render());
    }

    protected function makeForm()
    {
        if (! is_string($this->formClass) || ! class_exists($this->formClass)) {
            throw new InvalidArgumentException('GridModalRowAction requires an existing form class.');
        }

        $factory = app($this->formClass);

        if (! method_exists($factory, 'make')) {
            throw new InvalidArgumentException("Form class [{$this->formClass}] must provide a make method.");
        }

        $form = $factory->make($this->params);

        if ($form instanceof LazyRenderable && method_exists($form, 'payload')) {
            $form->payload($this->params);
        }

        if (! $form instanceof Renderable && ! method_exists($form, 'render')) {
            throw new InvalidArgumentException("Form class [{$this->formClass}] must create a renderable form.");
        }

        return $form;
    }
}
