<?php

namespace LaravelAdminPanel\Http\Controllers;

use Illuminate\Http\Request;
use LaravelAdminPanel\Facades\Admin;

class RoleController extends CrudController
{
    // POST (C)RUD
    public function store(Request $request)
    {
        $slug = $this->getSlug($request);

        $dataType = Admin::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('add', app($dataType->model_name));

        //Validate fields with ajax
        $val = $this->validateCrud($request->all(), $dataType->addRows);

        if ($val->fails()) {
            return response()->json(['errors' => $val->messages()]);
        }

        if (!$request->ajax()) {
            $data = new $dataType->model_name();
            $this->insertUpdateData($request, $slug, $dataType->addRows, $data);

            $data->permissions()->sync($request->input('permissions', []));

            return redirect()
            ->route("admin.{$dataType->slug}.index")
            ->with([
                'message'    => __('admin.generic.successfully_added_new')." {$dataType->display_name_singular}",
                'alert-type' => 'success',
                ]);
        }
    }

    // POST CR(U)D
    public function update(Request $request, $id)
    {
        $slug = $this->getSlug($request);

        $dataType = Admin::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('edit', app($dataType->model_name));

        //Validate fields with ajax
        $val = $this->validateCrud($request->all(), $dataType->editRows);

        if ($val->fails()) {
            return response()->json(['errors' => $val->messages()]);
        }

        if (!$request->ajax()) {
            $data = call_user_func([$dataType->model_name, 'findOrFail'], $id);
            $this->insertUpdateData($request, $slug, $dataType->editRows, $data);

            $data->permissions()->sync($request->input('permissions', []));

            return redirect()
            ->route("admin.{$dataType->slug}.index")
            ->with([
                'message'    => __('admin.generic.successfully_updated')." {$dataType->display_name_singular}",
                'alert-type' => 'success',
                ]);
        }
    }
}
