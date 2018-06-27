<?php

namespace LaravelAdminPanel\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use LaravelAdminPanel\Database\DatabaseUpdater;
use LaravelAdminPanel\Database\Schema\Identifier;
use LaravelAdminPanel\Database\Schema\SchemaManager;
use LaravelAdminPanel\Database\Schema\Table;
use LaravelAdminPanel\Database\Types\Type;
use LaravelAdminPanel\Events\TableAdded;
use LaravelAdminPanel\Events\TableDeleted;
use LaravelAdminPanel\Events\TableUpdated;
use LaravelAdminPanel\Facades\Admin;

class DatabaseController extends BaseController
{
    public function index()
    {
        Admin::canOrFail('browse_database');

        $dataTypes = Admin::model('DataType')->select('id', 'name', 'slug')->get();

        $tables = array_map(function ($table) use ($dataTypes) {
            $dataType = $dataTypes->where('name', $table)->first();
            $table = [
                'name'          => $table,
                'slug'          => $dataType ? $dataType->slug : null,
                'dataTypeId'    => $dataType ? $dataType->id : null,
            ];

            return (object) $table;
        }, SchemaManager::listTableNames());

        return Admin::view('admin::tools.database.index')->with(compact('dataTypes', 'tables'));
    }

    /**
     * Create database table.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function create()
    {
        Admin::canOrFail('browse_database');

        $db = $this->prepareDbManager('create');

        return Admin::view('admin::tools.database.edit-add', compact('db'));
    }

    /**
     * Store new database table.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        Admin::canOrFail('browse_database');

        try {
            Type::registerCustomPlatformTypes();

            $table = Table::make($request->table);
            SchemaManager::createTable($table);

            if (isset($request->create_model) && $request->create_model == 'on') {
                $modelNamespace = config('admin.models.namespace', app()->getNamespace());
                $params = [
                    'name' => $modelNamespace.Str::studly(Str::singular($table->name)),
                ];

                // if (in_array('deleted_at', $request->input('field.*'))) {
                //     $params['--softdelete'] = true;
                // }

                if (isset($request->create_migration) && $request->create_migration == 'on') {
                    $params['--migration'] = true;
                }

                Artisan::call('admin:make:model', $params);

                event(new TableAdded($table));
            } elseif (isset($request->create_migration) && $request->create_migration == 'on') {
                Artisan::call('make:migration', [
                    'name'    => 'create_'.$table->name.'_table',
                    '--table' => $table->name,
                ]);
            }

            return redirect()
               ->route('admin.database.index', request()->query())
               ->with($this->alertSuccess(__('admin.database.success_create_table', ['table' => $table->name])));
        } catch (Exception $e) {
            return back()->with($this->alertException($e))->withInput();
        }
    }

    /**
     * Edit database table.
     *
     * @param string $table
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function edit($table)
    {
        Admin::canOrFail('browse_database');

        if (!SchemaManager::tableExists($table)) {
            return redirect()
                ->route('admin.database.index')
                ->with($this->alertError(__('admin.database.edit_table_not_exist')));
        }

        $db = $this->prepareDbManager('update', $table);

        return Admin::view('admin::tools.database.edit-add', compact('db'));
    }

    /**
     * Update database table.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request)
    {
        Admin::canOrFail('browse_database');

        $table = json_decode($request->table, true);

        try {
            DatabaseUpdater::update($table);
            // TODO: synch BREAD with Table
            // $this->cleanOldAndCreateNew($request->original_name, $request->name);
            event(new TableUpdated($table));
        } catch (Exception $e) {
            return back()->with($this->alertException($e))->withInput();
        }

        return redirect()
               ->route('admin.database.index', $request->query())
               ->with($this->alertSuccess(__('admin.database.success_create_table', ['table' => $table['name']])));
    }

    protected function prepareDbManager($action, $table = '')
    {
        $db = new \stdClass();

        // Need to get the types first to register custom types
        $db->types = Type::getPlatformTypes();

        if ($action == 'update') {
            $db->table = SchemaManager::listTableDetails($table);
            $db->formAction = admin_route('database.update', $table);
        } else {
            $db->table = new Table('New Table');

            // Add prefilled columns
            $db->table->addColumn('id', 'integer', [
                'unsigned'      => true,
                'notnull'       => true,
                'autoincrement' => true,
            ]);

            $db->table->setPrimaryKey(['id'], 'primary');

            $db->formAction = admin_route('database.store');
        }

        $oldTable = old('table');
        $db->oldTable = $oldTable ? $oldTable : json_encode(null);
        $db->action = $action;
        $db->identifierRegex = Identifier::REGEX;
        $db->platform = SchemaManager::getDatabasePlatform()->getName();

        return $db;
    }

    public function cleanOldAndCreateNew($originalName, $tableName)
    {
        if (!empty($originalName) && $originalName != $tableName) {
            $dt = DB::table('data_types')->where('name', $originalName);
            if ($dt->get()) {
                $dt->delete();
            }

            $perm = DB::table('permissions')->where('table_name', $originalName);
            if ($perm->get()) {
                $perm->delete();
            }

            $params = ['name' => Str::studly(Str::singular($tableName))];
            Artisan::call('admin:make:model', $params);
        }
    }

    public function reorder_column(Request $request)
    {
        Admin::canOrFail('browse_database');

        if ($request->ajax()) {
            $table = $request->table;
            $column = $request->column;
            $after = $request->after;
            if ($after == null) {
                // SET COLUMN TO THE TOP
                DB::query("ALTER $table MyTable CHANGE COLUMN $column FIRST");
            }

            return 1;
        }

        return 0;
    }

    public function show($table)
    {
        Admin::canOrFail('browse_database');

        return response()->json(SchemaManager::describeTable($table));
    }

    public function destroy($table)
    {
        Admin::canOrFail('browse_database');

        try {
            SchemaManager::dropTable($table);
            event(new TableDeleted($table));

            return redirect()
                ->route('admin.database.index', request()->query())
                ->with($this->alertSuccess(__('admin.database.success_delete_table', ['table' => $table])));
        } catch (Exception $e) {
            return back()->with($this->alertException($e));
        }
    }
}
