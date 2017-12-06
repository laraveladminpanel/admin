<?php

namespace LaravelAdminPanel\FormFields;

use Illuminate\Http\Request;

class HiddenHandler extends AbstractHandler
{
    protected $codename = 'hidden';

    public function createContent($row, $dataType, $dataTypeContent, $options)
    {
        return view('admin::formfields.hidden', [
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
