<?php

namespace LaravelAdminPanel\FormFields;

use Illuminate\Http\Request;

class CustomHandler extends AbstractHandler
{
    protected $codename = 'custom';

    public function createContent($row, $dataType, $dataTypeContent, $options)
    {
        return view('admin::formfields.custom.' . $options->formfields_custom, [
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

    public function getContentForList(Request $request, $slug, $dataRow, $dataTypeContent)
    {
        $options = json_decode($dataRow->details);
        return view('admin::formfields.' . $options->view_path, [
            'dataRow'         => $dataRow,
            'options'         => $options,
            'slug'            => $slug,
            'dataTypeContent' => $dataTypeContent,
        ]);
    }
}
