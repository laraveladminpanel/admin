<?php

namespace LaravelAdminPanel\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LaravelAdminPanel\Database\Schema\SchemaManager;
use LaravelAdminPanel\Events\CrudAdded;
use LaravelAdminPanel\Events\CrudDeleted;
use LaravelAdminPanel\Events\CrudUpdated;
use LaravelAdminPanel\Facades\Admin;
use LaravelAdminPanel\Models\DataRow;

class DatabaseCrudController extends BaseController
{
    public function __construct()
    {
        list(, $action) = explode('@', \Route::getCurrentRoute()->getActionName());
        view()->share(compact('action'));
    }

    /**
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function add(Request $request, $table)
    {
        Admin::canOrFail('browse_database');

        $data = $this->prepopulateInfo($table);
        $data['fieldOptions'] = SchemaManager::describeTable($table);

        return Admin::view('admin::tools.database.edit-add-crud', $data);
    }

    private function prepopulateInfo($table)
    {
        $displayName = Str::singular(implode(' ', explode('_', Str::title($table))));
        $modelNamespace = config('admin.models.namespace', app()->getNamespace());
        if (empty($modelNamespace)) {
            $modelNamespace = app()->getNamespace();
        }

        return [
            'isModelTranslatable'  => true,
            'table'                => $table,
            'slug'                 => Str::slug($table),
            'display_name'         => $displayName,
            'display_name_plural'  => Str::plural($displayName),
            'model_name'           => $modelNamespace.Str::studly(Str::singular($table)),
            'generate_permissions' => true,
            'paginations'          => ['js', 'ajax', 'php']
        ];
    }

    public function store(Request $request)
    {
        Admin::canOrFail('browse_database');

        try {
            $dataType = Admin::model('DataType');
            $res = $dataType->updateDataType($request->all(), true);
            $data = $res
                ? $this->alertSuccess(__('admin.database.success_created_crud'))
                : $this->alertError(__('admin.database.error_creating_crud'));
            if ($res) {
                event(new CrudAdded($dataType, $data));
            }

            return redirect()->route('admin.database.index', $request->query())->with($data);
        } catch (Exception $e) {
            return redirect()->route('admin.database.index', $request->query())->with($this->alertException($e, 'Saving Failed'));
        }
    }

    public function edit($slug)
    {
        Admin::canOrFail('browse_database');

        $dataType = Admin::model('DataType')->whereSlug($slug)->firstOrFail();

        if (isset($dataType->model_name) && $dataType->model_name) {
            $fieldOptions = SchemaManager::describeTableFromModel($dataType->model_name);
        } else {
            $fieldOptions = SchemaManager::describeTable($dataType->name);
        }

        $isModelTranslatable = is_crud_translatable($dataType);
        $tables = SchemaManager::listTableNames();
        $dataTypeRelationships = Admin::model('DataRow')
            ->where('data_type_id', '=', $dataType->id)
            ->where('type', '=', 'relationship')
            ->get();

        $paginations = ['js', 'ajax', 'php'];

        $additionalTables = (object) Admin::model('DataType')
            ->select('id', 'name', 'slug')
            ->where('name', $dataType->name)
            ->where('slug', '!=', $dataType->slug)
            ->get();

        return Admin::view('admin::tools.database.edit-add-crud', compact(
            'dataType',
            'fieldOptions',
            'isModelTranslatable',
            'tables',
            'dataTypeRelationships',
            'paginations',
            'additionalTables'
        ));
    }

    public function clone($slug)
    {
        Admin::canOrFail('browse_database');

        $dataType = Admin::model('DataType')->whereSlug($slug)->firstOrFail();

        $fieldOptions = SchemaManager::describeTable($dataType->name);

        $isModelTranslatable = is_crud_translatable($dataType);
        $tables = SchemaManager::listTableNames();
        $dataTypeRelationships = Admin::model('DataRow')
            ->where('data_type_id', '=', $dataType->id)
            ->where('type', '=', 'relationship')
            ->get();

        $paginations = ['js', 'ajax', 'php'];

        $additionalTables = (object) Admin::model('DataType')
            ->select('id', 'name', 'slug')
            ->where('name', $dataType->name)
            ->where('slug', '!=', $dataType->slug)
            ->get();

        return Admin::view('admin::tools.database.edit-add-crud', compact(
            'dataType',
            'fieldOptions',
            'isModelTranslatable',
            'tables',
            'dataTypeRelationships',
            'paginations',
            'additionalTables'
        ));
    }

    public function update(Request $request, $id)
    {
        Admin::canOrFail('browse_database');

        /* @var \LaravelAdminPanel\Models\DataType $dataType */
        try {
            $dataType = Admin::model('DataType')->findOrFail($id);

            // Prepare Translations and Transform data
            $translations = is_crud_translatable($dataType)
                ? $dataType->prepareTranslations($request)
                : [];

            $res = $dataType->updateDataType($request->all(), true);

            $data = $res
                ? $this->alertSuccess(__('admin.database.success_update_crud', ['datatype' => $dataType->name]))
                : $this->alertError(__('admin.database.error_updating_crud'));
            if ($res) {
                event(new CrudUpdated($dataType, $data));
            }

            // Save translations if applied
            $dataType->saveTranslations($translations);

            return redirect()->route('admin.database.index', $request->query())->with($data);
        } catch (Exception $e) {
            return back()->with($this->alertException($e, __('admin.generic.update_failed')));
        }
    }

    public function delete($id)
    {
        Admin::canOrFail('browse_database');

        /* @var \LaravelAdminPanel\Models\DataType $dataType */
        $dataType = Admin::model('DataType')->findOrFail($id);

        // Delete Translations, if present
        if (is_crud_translatable($dataType)) {
            $dataType->deleteAttributeTranslations($dataType->getTranslatableAttributes());
        }

        $res = Admin::model('DataType')->destroy($id);
        $data = $res
            ? $this->alertSuccess(__('admin.database.success_remove_crud', ['datatype' => $dataType->name]))
            : $this->alertError(__('admin.database.error_updating_crud'));
        if ($res) {
            event(new CrudDeleted($dataType, $data));
        }

        if (!is_null($dataType)) {
            Admin::model('Permission')->removeFrom($dataType->name);
        }

        return redirect()->route('admin.database.index', request()->query())->with($data);
    }

    public function addRelationship(Request $request)
    {
        $relationshipField = $this->getRelationshipField($request);

        if (!class_exists($request->relationship_model)) {
            return back()->with([
                    'message'    => 'Model Class '.$request->relationship_model.' does not exist. Please create Model before creating relationship.',
                    'alert-type' => 'error',
                ]);
        }

        try {
            DB::beginTransaction();

            $relationship_column = $request->relationship_column_belongs_to;
            if ($request->relationship_type == 'hasOne' || $request->relationship_type == 'hasMany') {
                $relationship_column = $request->relationship_column;
            }

            // Build the relationship details
            $relationshipDetails = json_encode([
                'model'       => $request->relationship_model,
                'table'       => $request->relationship_table,
                'type'        => $request->relationship_type,
                'column'      => $relationship_column,
                'key'         => $request->relationship_key,
                'label'       => $request->relationship_label,
                'pivot_table' => $request->relationship_pivot,
                'pivot'       => ($request->relationship_type == 'belongsToMany') ? '1' : '0',
            ]);

            $newRow = new DataRow();

            $newRow->data_type_id = $request->data_type_id;
            $newRow->field = $relationshipField;
            $newRow->type = 'relationship';
            $newRow->display_name = $request->relationship_table;
            $newRow->required = 0;

            foreach (['browse', 'read', 'edit', 'add', 'delete'] as $check) {
                $newRow->{$check} = 1;
            }

            $newRow->details = $relationshipDetails;
            $newRow->order = intval(Admin::model('DataType')->find($request->data_type_id)->lastRow()->order) + 1;

            if (!$newRow->save()) {
                return back()->with([
                    'message'    => 'Error saving new relationship row for '.$request->relationship_table,
                    'alert-type' => 'error',
                ]);
            }

            DB::commit();

            return back()->with([
                'message'    => 'Successfully created new relationship for '.$request->relationship_table,
                'alert-type' => 'success',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with([
                'message'    => 'Error creating new relationship: '.$e->getMessage(),
                'alert-type' => 'error',
            ]);
        }
    }

    private function getRelationshipField($request)
    {
        // We need to make sure that we aren't creating an already existing field

        $dataType = Admin::model('DataType')->find($request->data_type_id);

        $field = str_singular($dataType->name).'_'.$request->relationship_type.'_'.str_singular($request->relationship_table).'_relationship';

        $relationshipFieldOriginal = $relationshipField = strtolower($field);

        $existingRow = Admin::model('DataRow')->where('field', '=', $relationshipField)->first();
        $index = 1;

        while (isset($existingRow->id)) {
            $relationshipField = $relationshipFieldOriginal.'_'.$index;
            $existingRow = Admin::model('DataRow')->where('field', '=', $relationshipField)->first();
            $index += 1;
        }

        return $relationshipField;
    }

    public function deleteRelationship($id)
    {
        Admin::model('DataRow')->destroy($id);

        return back()->with([
                'message'    => 'Successfully deleted relationship.',
                'alert-type' => 'success',
            ]);
    }
}
