<?php

namespace LaravelAdminPanel\FormFields;

use Illuminate\Http\Request;
use LaravelAdminPanel\Facades\Admin;
use LaravelAdminPanel\Traits\Renderable;

abstract class AbstractHandler implements HandlerInterface
{
    use Renderable;

    protected $name;
    protected $codename;
    protected $supports = [];

    abstract public function getContentBasedOnType(Request $request, $slug, $row);

    public static function initial($content)
    {
        $handler = __NAMESPACE__ . '\\' . studly_case($content) . "Handler";
        return new $handler();
    }

    public function handle($row, $dataType, $dataTypeContent)
    {
        $content = $this->createContent(
            $row,
            $dataType,
            $dataTypeContent,
            json_decode($row->details)
        );

        return $this->render($content);
    }

    public function supports($driver)
    {
        if (empty($this->supports)) {
            return true;
        }

        return in_array($driver, $this->supports);
    }

    public function getCodename()
    {
        if (empty($this->codename)) {
            $name = class_basename($this);

            if (ends_with($name, 'Handler')) {
                $name = substr($name, 0, -strlen('Handler'));
            }

            $this->codename = snake_case($name);
        }

        return $this->codename;
    }

    public function getName()
    {
        if (empty($this->name)) {
            $this->name = ucwords(str_replace('_', ' ', $this->getCodename()));
        }

        return $this->name;
    }

    public function getContentForList(Request $request, $slug, $dataType, $dataTypeContent)
    {
        $view = 'admin::formfields.list.' . $this->codename;

        if (!view()->exists($view)) {
            return $dataTypeContent->{$dataType->field};
        }

        $options = json_decode($dataType->details);

        return Admin::view('admin::formfields.list.' . $this->codename, compact('dataTypeContent', 'dataType', 'options'));
    }
}
