<?php

namespace LaravelAdminPanel\FormFields;

use Illuminate\Http\Request;

class DateHandler extends AbstractHandler
{
    protected $codename = 'date';

    public function createContent($row, $dataType, $dataTypeContent, $options)
    {
        return view('admin::formfields.date', [
            'row'             => $row,
            'options'         => $options,
            'dataType'        => $dataType,
            'dataTypeContent' => $dataTypeContent,
        ]);
    }

    public function getContentBasedOnType(Request $request, $slug, $row)
    {
        $content = $request->input($row->field);
        if (in_array($request->method(), ['PUT', 'POST'])) {
            if (empty($request->input($row->field))) {
                $content = null;
            } else {
                $content = gmdate('Y-m-d H:i:s', strtotime($request->input($row->field)));
            }
        }

        return $content;
    }
}
