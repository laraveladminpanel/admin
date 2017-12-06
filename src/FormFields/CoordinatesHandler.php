<?php

namespace LaravelAdminPanel\FormFields;

use Illuminate\Http\Request;

class CoordinatesHandler extends AbstractHandler
{
    protected $supports = [
        'mysql',
        'pgsql',
    ];

    protected $codename = 'coordinates';

    public function createContent($row, $dataType, $dataTypeContent, $options)
    {
        return view('admin::formfields.coordinates', [
            'row'             => $row,
            'options'         => $options,
            'dataType'        => $dataType,
            'dataTypeContent' => $dataTypeContent,
        ]);
    }

    public function getContentBasedOnType(Request $request, $slug, $row)
    {
        $content = null;

        if (empty($coordinates = $request->input($row->field))) {
            $content = null;
        } else {
            //DB::connection()->getPdo()->quote won't work as it quotes the
            // lat/lng, which leads to wrong Geometry type in POINT() MySQL constructor
            $lat = (float) ($coordinates['lat']);
            $lng = (float) ($coordinates['lng']);
            $content = DB::raw('ST_GeomFromText(\'POINT('.$lat.' '.$lng.')\')');
        }

        return $content;
    }
}
