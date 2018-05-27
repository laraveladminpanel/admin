<?php

namespace LaravelAdminPanel\FormFields;

use Illuminate\Http\Request;

class CheckboxHandler extends AbstractHandler
{
    protected $codename = 'checkbox';

    public function createContent($row, $dataType, $dataTypeContent, $options)
    {
        return view('admin::formfields.checkbox', [
            'row'             => $row,
            'options'         => $options,
            'dataType'        => $dataType,
            'dataTypeContent' => $dataTypeContent,
        ]);
    }

    public function getContentBasedOnType(Request $request, $slug, $row)
    {
        $checkBoxRow = $request->input($row->field);

        if (isset($checkBoxRow)) {
            return 1;
        }

        return 0;
    }
}
