<?php

namespace LaravelAdminPanel\Http\Controllers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use LaravelAdminPanel\Database\Schema\SchemaManager;
use LaravelAdminPanel\Events\CrudDataAdded;
use LaravelAdminPanel\Events\CrudDataDeleted;
use LaravelAdminPanel\Events\CrudDataUpdated;
use LaravelAdminPanel\Events\CrudImagesDeleted;
use LaravelAdminPanel\Facades\Admin;
use LaravelAdminPanel\FormFields\AbstractHandler;
use LaravelAdminPanel\Http\Controllers\Traits\CrudRelationshipParser;
use Yajra\DataTables\Facades\DataTables;

class CrudController extends BaseController
{
    use CrudRelationshipParser;

    //***************************************
    //
    //      Browse our Data Type CRUD
    //
    //****************************************

    public function index(Request $request)
    {
        // GET THE SLUG, ex. 'posts', 'pages', etc.
        $slug = $this->getSlug($request);

        // GET THE DataType based on the slug
        $dataType = Admin::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('browse', app($dataType->model_name));

        if ($dataType->pagination === 'ajax') {
            return $this->indexAjax($request);
        }

        $getter = $dataType->pagination === 'php' ? 'paginate' : 'get';

        $search = (object) ['value' => $request->get('s'), 'key' => $request->get('key'), 'filter' => $request->get('filter')];
        $searchable = $dataType->pagination === 'php' ? array_keys(SchemaManager::describeTable(app($dataType->model_name)->getTable())->toArray()) : '';
        $orderBy = $request->get('order_by');
        $sortOrder = $request->get('sort_order', null);

        // Next Get or Paginate the actual content from the MODEL that corresponds to the slug DataType
        if (strlen($dataType->model_name) != 0) {
            $model = app($dataType->model_name);

            $query = $model;

            if (method_exists($model, 'adminList')) {
                $query = $model->adminList();
            }

            $query->select('*');

            $relationships = $this->getRelationships($dataType);

            // If a column has a relationship associated with it, we do not want to show that field
            $this->removeRelationshipField($dataType, 'browse');

            if ($search->value && $search->key && $search->filter) {
                $search_filter = ($search->filter == 'equals') ? '=' : 'LIKE';
                $search_value = ($search->filter == 'equals') ? $search->value : '%'.$search->value.'%';
                $query = $query->where($search->key, $search_filter, $search_value);
            }

            if ($orderBy && in_array($orderBy, $dataType->fields())) {
                $querySortOrder = (!empty($sortOrder)) ? $sortOrder : 'DESC';
                $dataTypeContent = call_user_func([
                    $query = $query->with($relationships)->orderBy($orderBy, $querySortOrder),
                    $getter,
                ]);
            } elseif ($model->timestamps) {
                $dataTypeContent = call_user_func([$query->latest($model::CREATED_AT), $getter]);
            } else {
                $dataTypeContent = call_user_func([$query->with($relationships)->orderBy($model->getKeyName(), 'DESC'), $getter]);
            }

            // Replace relationships' keys for labels and create READ links if a slug is provided.
            $dataTypeContent = $this->resolveRelations($dataTypeContent, $dataType);
        } else {
            // If Model doesn't exist, get data from table name
            $dataTypeContent = call_user_func([DB::table($dataType->name), $getter]);
            $model = false;
        }

        // Check if CRUD is Translatable
        if (($isModelTranslatable = is_crud_translatable($model))) {
            $dataTypeContent->load('translations');
        }

        // Check if server side pagination is enabled
        $isServerSide = $dataType->isServerSide();

        $view = 'admin::crud.browse';

        if (view()->exists("admin::$slug.browse")) {
            $view = "admin::$slug.browse";
        }

        return Admin::view($view, compact(
            'dataType',
            'dataTypeContent',
            'isModelTranslatable',
            'search',
            'orderBy',
            'sortOrder',
            'searchable',
            'isServerSide'
        ));
    }


    //***************************************
    //
    //      Ajax browse our Data Type
    //
    //****************************************

    private function indexAjax(Request $request)
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
        $pagination = $dataType->pagination;

        return Admin::view('admin::crud.browse-ajax', compact(
            'dataType',
            'slug',
            'isServerSide',
            'isModelTranslatable',
            'columns',
            'pagination'
        ));
    }


    //***************************************
    //
    //      Get Data for Ajax List
    //
    //****************************************
    public function getAjaxList(Request $request) // GET THE SLUG, ex. 'posts', 'pages', etc.
    {
        // GET THE SLUG, ex. 'posts', 'pages', etc.
        $slug = $request->slug;

        // GET THE DataType based on the slug
        $dataType = Admin::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('browse', app($dataType->model_name));

        $model = app($dataType->model_name);
        $query = $model->select('*');

        if (method_exists($model, 'adminList')) {
            $query = $model->adminList()->select('*');
        }

        if (!isset($request->order)) {
            if ($model->timestamps) {
                $query = $query->latest($model::CREATED_AT);
            } else {
                $relationships = $this->getRelationships($dataType);
                $query = $query->with($relationships)->orderBy($model->getKeyName(), 'DESC');
            }
        }

        $query = DataTables::of($query);

        foreach ($dataType->ajaxList() as $dataRow) {
            $query->editColumn($dataRow->field, function($dataTypeContent) use($request, $slug, $dataRow){
                $content = $dataTypeContent->{$dataRow->field};

                $handler = AbstractHandler::initial($dataRow->type);

                if (method_exists($handler, 'getContentForList')) {
                    $content = $handler->getContentForList($request, $slug, $dataRow, $dataTypeContent);
                }

                return $content;
            });

            if ($dataRow->type == 'relationship') {
                $query->filterColumn($dataRow->field, function ($query, $keyword) use($dataRow){
                    $relationship = json_decode($dataRow->details);
                    $ids = app($relationship->model)->where($relationship->label, 'like', "%$keyword%")->pluck($relationship->key);
                    $query->whereIn($relationship->column, $ids);
                });
            }
        }

        return $query
            ->addColumn('delete_checkbox', function($dataTypeContent) {
                return '<input type="checkbox" name="row_id" id="checkbox_' . $dataTypeContent->id . '" value="' . $dataTypeContent->id . '">';

            })
            ->addColumn('actions', function($dataTypeContent) use($dataType){
                return Admin::view('admin::list.datatable.buttons', ['data' => $dataTypeContent, 'dataType' => $dataType]);
            })
            ->rawColumns(array_merge($dataType->ajaxListFields(), ['delete_checkbox', 'actions']))
            ->make(true);
    }

    //***************************************
    //                ______
    //               |  ____|
    //               | |
    //               | |
    //               | |____
    //               |______|
    //
    //
    // Add a new item of our Data Type (C)RUD
    //
    //****************************************

    public function create(Request $request)
    {
        $slug = $this->getSlug($request);

        $dataType = Admin::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('add', app($dataType->model_name));

        $dataTypeContent = (strlen($dataType->model_name) != 0)
                            ? new $dataType->model_name()
                            : false;

        foreach ($dataType->addRows as $key => $row) {
            $details = json_decode($row->details);
            $dataType->addRows[$key]['col_width'] = isset($details->width) ? $details->width : 100;
        }

        // If a column has a relationship associated with it, we do not want to show that field
        $this->removeRelationshipField($dataType, 'add');

        // Check if CRUD is Translatable
        $isModelTranslatable = is_crud_translatable($dataTypeContent);

        $view = 'admin::crud.edit-add';

        if (view()->exists("admin::$slug.edit-add")) {
            $view = "admin::$slug.edit-add";
        }

        return Admin::view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable'));
    }

    /**
     * POST (C)RUD - Store data.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $slug = $this->getSlug($request);

        $dataType = Admin::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('edit', app($dataType->model_name));

        // Validate fields with ajax
        $val = $this->validateCrud($request->all(), $dataType->addRows);

        if ($val->fails()) {
            return response()->json(['errors' => $val->messages()]);
        }

        if (!$request->ajax()) {
            $data = $this->insertUpdateData($request, $slug, $dataType->addRows, new $dataType->model_name());

            event(new CrudDataAdded($request, $slug, $dataType, $data));

            $requestQuery = (object) $request->query();

            if ($this->needRedirectToParentCrud($requestQuery)) {
                return redirect()
                    ->route("admin.{$requestQuery->crud_slug}.{$requestQuery->crud_action}", $requestQuery->crud_id)
                    ->with([
                        'message'    => __('admin.generic.successfully_added_new')." {$dataType->display_name_singular}",
                        'alert-type' => 'success',
                    ]);
            }

            return redirect()
                ->route("admin.{$dataType->slug}.index", $request->query())
                ->with([
                        'message'    => __('admin.generic.successfully_added_new')." {$dataType->display_name_singular}",
                        'alert-type' => 'success',
                    ]);
        }
    }

    //***************************************
    //                _____
    //               |  __ \
    //               | |__) |
    //               |  _  /
    //               | | \ \
    //               |_|  \_\
    //
    //  Read an item of our Data Type C(R)UD
    //
    //****************************************

    public function show(Request $request, $id)
    {
        $slug = $this->getSlug($request);

        $dataType = Admin::model('DataType')->where('slug', '=', $slug)->first();

        // Compatibility with Model binding.
        $id = $id instanceof Model ? $id->{$id->getKeyName()} : $id;

        $relationships = $this->getRelationships($dataType);
        if (strlen($dataType->model_name) != 0) {
            $model = app($dataType->model_name);
            $dataTypeContent = call_user_func([$model->with($relationships), 'findOrFail'], $id);
        } else {
            // If Model doest exist, get data from table name
            $dataTypeContent = DB::table($dataType->name)->where('id', $id)->first();
        }

        // Replace relationships' keys for labels and create READ links if a slug is provided.
        $dataTypeContent = $this->resolveRelations($dataTypeContent, $dataType, true);

        // If a column has a relationship associated with it, we do not want to show that field
        $this->removeRelationshipField($dataType, 'read');

        // Check permission
        $this->authorize('read', $dataTypeContent);

        // Check if CRUD is Translatable
        $isModelTranslatable = is_crud_translatable($dataTypeContent);

        $view = 'admin::crud.read';

        if (view()->exists("admin::$slug.read")) {
            $view = "admin::$slug.read";
        }

        return Admin::view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable'));
    }

    //***************************************
    //                __   __
    //               | |  | |
    //               | |  | |
    //               | |  | |
    //               | |__| |
    //               |______|
    //
    //  Edit an item of our Data Type CR(U)D
    //
    //****************************************

    public function edit(Request $request, $id)
    {
        $slug = $this->getSlug($request);

        $dataType = Admin::model('DataType')->where('slug', '=', $slug)->first();

        // Compatibility with Model binding.
        $id = $id instanceof Model ? $id->{$id->getKeyName()} : $id;

        $relationships = $this->getRelationships($dataType);

        $dataTypeContent = (strlen($dataType->model_name) != 0)
            ? app($dataType->model_name)->with($relationships)->findOrFail($id)
            : DB::table($dataType->name)->where('id', $id)->first(); // If Model doest exist, get data from table name

        foreach ($dataType->editRows as $key => $row) {
            $details = json_decode($row->details);
            $dataType->editRows[$key]['col_width'] = isset($details->width) ? $details->width : 100;
        }

        // If a column has a relationship associated with it, we do not want to show that field
        $this->removeRelationshipField($dataType, 'edit');

        // Check permission
        $this->authorize('edit', $dataTypeContent);

        // Check if CRUD is Translatable
        $isModelTranslatable = is_crud_translatable($dataTypeContent);

        $view = 'admin::crud.edit-add';

        if (view()->exists("admin::$slug.edit-add")) {
            $view = "admin::$slug.edit-add";
        }

        return Admin::view($view, compact('dataType', 'dataTypeContent', 'isModelTranslatable'));
    }

    // POST CR(U)D
    public function update(Request $request, $id)
    {
        $slug = $this->getSlug($request);

        $dataType = Admin::model('DataType')->where('slug', '=', $slug)->first();

        // Compatibility with Model binding.
        $id = $id instanceof Model ? $id->{$id->getKeyName()} : $id;

        $data = call_user_func([$dataType->model_name, 'findOrFail'], $id);

        // Check permission
        $this->authorize('edit', $data);

        // Validate fields with ajax
        $val = $this->validateCrud($request->all(), $dataType->editRows);

        if ($val->fails()) {
            return response()->json(['errors' => $val->messages()]);
        }

        if (!$request->ajax()) {
            $this->insertUpdateData($request, $slug, $dataType->editRows, $data);

            event(new CrudDataUpdated($request, $slug, $dataType, $data));

            $requestQuery = (object) $request->query();

            if ($this->needRedirectToParentCrud($requestQuery)) {
                return redirect()
                    ->route("admin.{$requestQuery->crud_slug}.{$requestQuery->crud_action}", $requestQuery->crud_id)
                    ->with([
                        'message'    => __('admin.generic.successfully_updated')." {$dataType->display_name_singular}",
                        'alert-type' => 'success',
                    ]);
            }

            return redirect()
                ->route("admin.{$dataType->slug}.index", $request->query())
                ->with([
                    'message'    => __('admin.generic.successfully_updated')." {$dataType->display_name_singular}",
                    'alert-type' => 'success',
                ]);
        }
    }

    //***************************************
    //                _____
    //               |  __ \
    //               | |  | |
    //               | |  | |
    //               | |__| |
    //               |_____/
    //
    //         Delete an item CRU(D)
    //
    //****************************************

    public function destroy(Request $request, $id)
    {
        $slug = $this->getSlug($request);

        $dataType = Admin::model('DataType')->where('slug', '=', $slug)->first();

        // Check permission
        $this->authorize('delete', app($dataType->model_name));

        // Init array of IDs
        $ids = [];
        if (empty($id)) {
            // Bulk delete, get IDs from POST
            $ids = explode(',', $request->ids);
        } else {
            // Single item delete, get ID from URL or Model Binding
            $ids[] = $id instanceof Model ? $id->{$id->getKeyName()} : $id;
        }
        foreach ($ids as $id) {
            $data = call_user_func([$dataType->model_name, 'findOrFail'], $id);
            $this->cleanup($dataType, $data);
        }

        $displayName = count($ids) > 1 ? $dataType->display_name_plural : $dataType->display_name_singular;

        $res = $data->destroy($ids);

        $data = $res
            ? [
                'message'    => __('admin.generic.successfully_deleted')." {$displayName}",
                'alert-type' => 'success',
            ]
            : [
                'message'    => __('admin.generic.error_deleting')." {$displayName}",
                'alert-type' => 'error',
            ];

        if ($res) {
            event(new CrudDataDeleted($dataType, $data, $ids));
        }

        $requestQuery = (object) $request->query();

        if ($this->needRedirectToParentCrud($requestQuery)) {
            return redirect()
                ->route("admin.{$requestQuery->crud_slug}.{$requestQuery->crud_action}", $requestQuery->crud_id)
                ->with([
                    'message'    => __('admin.generic.successfully_added_new')." {$dataType->display_name_singular}",
                    'alert-type' => 'success',
                ]);
        }

        return redirect()->route("admin.{$dataType->slug}.index", $request->query())->with($data);
    }

    /**
     * Remove translations, images and files related to a CRUD item.
     *
     * @param \Illuminate\Database\Eloquent\Model $dataType
     * @param \Illuminate\Database\Eloquent\Model $data
     *
     * @return void
     */
    protected function cleanup($dataType, $data)
    {
        // Delete Translations, if present
        if (is_crud_translatable($data)) {
            $data->deleteAttributeTranslations($data->getTranslatableAttributes());
        }

        // Delete Images
        $this->deleteCrudImages($data, $dataType->deleteRows->where('type', 'image'));

        // Delete Files
        foreach ($dataType->deleteRows->where('type', 'file') as $row) {
            $files = json_decode($data->{$row->field});
            if ($files) {
                foreach ($files as $file) {
                    $this->deleteFileIfExists($file->download_link);
                }
            }
        }
    }

    /**
     * Delete all images related to a CRUD item.
     *
     * @param \Illuminate\Database\Eloquent\Model $data
     * @param \Illuminate\Database\Eloquent\Model $rows
     *
     * @return void
     */
    public function deleteCrudImages($data, $rows)
    {
        foreach ($rows as $row) {
            $this->deleteFileIfExists($data->{$row->field});

            $options = json_decode($row->details);

            if (isset($options->thumbnails)) {
                foreach ($options->thumbnails as $thumbnail) {
                    $ext = explode('.', $data->{$row->field});
                    $extension = '.'.$ext[count($ext) - 1];

                    $path = str_replace($extension, '', $data->{$row->field});

                    $thumb_name = $thumbnail->name;

                    $this->deleteFileIfExists($path.'-'.$thumb_name.$extension);
                }
            }
        }

        if ($rows->count() > 0) {
            event(new CrudImagesDeleted($data, $rows));
        }
    }

    /**
     * Check if need to redirect to the parent CRUD.
     *
     * @return boolean
     */
    private function needRedirectToParentCrud(\stdClass $requestQuery)
    {
        return isset($requestQuery->crud_slug) && $requestQuery->crud_slug
            && isset($requestQuery->crud_action) && $requestQuery->crud_action
            && isset($requestQuery->crud_id) && $requestQuery->crud_id;
    }
}
