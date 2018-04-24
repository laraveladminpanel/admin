<?php

namespace LaravelAdminPanel\FormFields;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

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
}
