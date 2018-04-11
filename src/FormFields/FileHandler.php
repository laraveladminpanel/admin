<?php

namespace LaravelAdminPanel\FormFields;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class FileHandler extends AbstractHandler
{
    protected $codename = 'file';

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
        $content = null;

        if ($files = $request->file($row->field)) {
            if (!is_array($files)) {
                $files = [$files];
            }
            $filesPath = [];
            foreach ($files as $key => $file) {
                $filename = Str::random(20);
                $path = $slug.'/'.date('FY').'/';
                $file->storeAs(
                    $path,
                    $filename.'.'.$file->getClientOriginalExtension(),
                    config('admin.storage.disk', 'public')
                );
                array_push($filesPath, [
                    'download_link' => $path.$filename.'.'.$file->getClientOriginalExtension(),
                    'original_name' => $file->getClientOriginalName(),
                ]);
            }

            return json_encode($filesPath);
        }

        return $content;
    }
}
