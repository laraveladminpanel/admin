<?php

namespace LaravelAdminPanel\Http\Controllers;

use Illuminate\Http\Request;
use Validator;

class ApiController extends BaseController
{
    public function order(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'data' => 'required|json',
            'table_name' => 'required|string',
            'order_by' => 'required|string',
        ]);

        if (!$validator->passes()) {
            return response()->json(['error' => $validator->errors()->all()], 400);
        }

        $data = json_decode($request->data);
        $table_name = $request->table_name;
        $order_by = $request->order_by;

        foreach ($data as $index => $item) {
            \DB::table($table_name)
                ->where('id', $item->id)
                ->update([$order_by => $index + 1]);
        }

        return response()->json(['success' => __('Sorted')]);
    }
}
