<?php

namespace LaravelAdminPanel\FormFields;

use Illuminate\Http\Request;

class SelectMultipleHandler extends AbstractHandler
{
    protected $codename = 'select_multiple';

    public function createContent($row, $dataType, $dataTypeContent, $options)
    {
        return view('admin::formfields.select_multiple', [
            'row'             => $row,
            'options'         => $options,
            'dataType'        => $dataType,
            'dataTypeContent' => $dataTypeContent,
        ]);
    }

    public function getContentBasedOnType(Request $request, $slug, $row)
    {
        $content = $request->input($row->field);

        if ($content === null) {
            $content = [];
        } else {
            // Check if we need to parse the editablePivotFields to update fields in the corresponding pivot table
            $options = json_decode($row->details);
            if (isset($options->relationship) && !empty($options->relationship->editablePivotFields)) {
                $pivotContent = [];
                // Read all values for fields in pivot tables from the request
                foreach ($options->relationship->editablePivotFields as $pivotField) {
                    if (!isset($pivotContent[$pivotField])) {
                        $pivotContent[$pivotField] = [];
                    }
                    $pivotContent[$pivotField] = $request->input('pivot_'.$pivotField);
                }
                // Create a new content array for updating pivot table
                $newContent = [];
                foreach ($content as $contentIndex => $contentValue) {
                    $newContent[$contentValue] = [];
                    foreach ($pivotContent as $pivotContentKey => $value) {
                        $newContent[$contentValue][$pivotContentKey] = $value[$contentIndex];
                    }
                }
                $content = $newContent;
            }
        }

        return json_encode($content);
    }
}
