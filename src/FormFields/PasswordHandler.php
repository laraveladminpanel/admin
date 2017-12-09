<?php

namespace LaravelAdminPanel\FormFields;

use Illuminate\Http\Request;

class PasswordHandler extends AbstractHandler
{
    protected $codename = 'password';

    public function createContent($row, $dataType, $dataTypeContent, $options)
    {
        return view('admin::formfields.password', [
            'row'             => $row,
            'options'         => $options,
            'dataType'        => $dataType,
            'dataTypeContent' => $dataTypeContent,
        ]);
    }

    public function getContentBasedOnType(Request $request, $slug, $row)
    {
        $pass_field = $request->input($row->field);

        if (isset($pass_field) && !empty($pass_field)) {
            return bcrypt($request->input($row->field));
        }

        return null;
    }
}
