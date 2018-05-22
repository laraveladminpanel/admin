<?php

namespace LaravelAdminPanel\Http\Controllers;

use Illuminate\Http\Request;
use LaravelAdminPanel\Facades\Admin;
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

    public function getAjaxList($slug) // GET THE SLUG, ex. 'posts', 'pages', etc.
    {
        // GET THE DataType based on the slug
        $dataType = Admin::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('browse', app($dataType->model_name));

        $model = app($dataType->model_name);

        $query = Datatables::of($model->query())
            ->addColumn('delete_checkbox', function($row) {
                return '<input type="checkbox" name="row_id" id="checkbox_' . $row->id . '" value="' . $row->id . '">';

            })
            ->addColumn('actions', function($row) use($dataType){
                return Admin::view('admin::list.datatable.buttons', ['data' => $row, 'dataType' => $dataType]);
            });

        return $query
            ->rawColumns(['actions', 'delete_checkbox'])
            ->make(true);

        //@elseif($row->type == 'relationship')
        //@include('admin::formfields.relationship', ['view' => 'browse'])
    }
}
