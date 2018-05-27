<?php

namespace LaravelAdminPanel\FormFields;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use LaravelAdminPanel\Facades\Admin;

class RelationshipHandler extends AbstractHandler
{
    public function createContent($row, $dataType, $dataTypeContent, $options)
    {
        return view('admin::formfields.file', [
            'row'             => $row,
            'options'         => $options,
            'dataType'        => $dataType,
            'dataTypeContent' => $dataTypeContent,
        ]);
    }

    public function getContentBasedOnType(Request $request, $slug, $row)
    {
        return $request->input($row->field);
    }

    public function getContentForList(Request $request, $slug, $dataType, $dataTypeContent)
    {
        $options = json_decode($dataType->details);

        return Admin::view('admin::formfields.relationship.' . $options->type, [
            'options' => $options,
            'dataTypeContent' => $dataTypeContent,
            'view' => 'browse'
        ]);
    }
}
