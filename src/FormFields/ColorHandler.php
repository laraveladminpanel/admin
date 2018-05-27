<?php

namespace LaravelAdminPanel\FormFields;

use Illuminate\Http\Request;

class ColorHandler extends AbstractHandler
{
    protected $codename = 'color';

    public function createContent($row, $dataType, $dataTypeContent, $options)
    {
        return view('admin::formfields.color', [
            'row'             => $row,
            'options'         => $options,
            'dataType'        => $dataType,
            'dataTypeContent' => $dataTypeContent,
        ]);
    }

    public function getContentBasedOnType(Request $request, $slug, $row)
    {
        $content = $request->input($row->field);
        $options = json_decode($row->details);
        if (isset($options->null)) {
            return $content == $options->null ? null : $content;
        }

        return $content;
    }
}
