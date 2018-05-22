<?php

namespace LaravelAdminPanel\Http\Controllers;

use Illuminate\Http\Request;
use LaravelAdminPanel\Facades\Admin;
use LaravelAdminPanel\FormFields\AbstractHandler;
use Yajra\DataTables\Facades\DataTables;

class CrudAjaxController extends BaseController
{
    //***************************************
    //
    //      Browse our Data Type CRUD
    //
    //****************************************

    public function index(Request $request, DataTables $datatables)
    {
        // GET THE SLUG, ex. 'posts', 'pages', etc.
        $slug = $this->getSlug($request);

        // GET THE DataType based on the slug
        $dataType = Admin::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('browse', app($dataType->model_name));

        $isServerSide = false;
        $isModelTranslatable = false;
        $columns = $dataType->fields();

        return Admin::view('admin::crud.browse-ajax', compact(
            'dataType',
            'slug',
            'isServerSide',
            'isModelTranslatable',
            'columns'
        ));
    }

    public function getAjaxList(Request $request, $slug) // GET THE SLUG, ex. 'posts', 'pages', etc.
    {
        // GET THE DataType based on the slug
        $dataType = Admin::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('browse', app($dataType->model_name));

        $model = app($dataType->model_name);

        $query = Datatables::of($model->query())
            ->addColumn('delete_checkbox', function($dataTypeContent) {
                return '<input type="checkbox" name="row_id" id="checkbox_' . $dataTypeContent->id . '" value="' . $dataTypeContent->id . '">';

            })
            ->addColumn('actions', function($dataTypeContent) use($dataType){
                return Admin::view('admin::list.datatable.buttons', ['data' => $dataTypeContent, 'dataType' => $dataType]);
            });

            foreach ($dataType->ajaxList() as $dataRow) {
                $query->addColumn($dataRow->field, function($dataTypeContent) use($request, $slug, $dataRow){
                    $content = $dataTypeContent->{$dataRow->field};

                    $handler = AbstractHandler::initial($dataRow->type);

                    if (method_exists($handler, 'getContentForList')) {
                        $content = $handler->getContentForList($request, $slug, $dataRow, $dataTypeContent);
                    }

                    return $content;
                });
            }

        return $query
            ->rawColumns(array_merge($dataType->ajaxListFields(), ['actions', 'delete_checkbox']))
            ->make(true);
    }
}
