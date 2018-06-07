<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use LaravelAdminPanel\Models\DataType;
use LaravelAdminPanel\Models\Permission;

class AlterPermissionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasColumn('permissions', 'slug')) {
            return false;
        }

        Schema::table('permissions', function (Blueprint $table) {
            $table->renameColumn('table_name', 'slug');
        });

        $dataTypes = DataType::all();
        $permissions = Permission::whereNotNull('slug')->get();

        foreach ($permissions as $permission) {
            $dataType = $dataTypes->where('name', $permission->slug)->first();

            if ($dataType && $dataType->slug) {
                $permission->key = str_replace($dataType->name, $dataType->slug, $permission->key);
                $permission->slug = $dataType->slug;
                $permission->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('permissions', 'table_name')) {
            return false;
        }

        Schema::table('permissions', function (Blueprint $table) {
            $table->renameColumn('slug', 'table_name');
        });

        $dataTypes = DataType::all();
        $permissions = Permission::whereNotNull('table_name')->get();

        foreach ($permissions as $permission) {
            $dataType = $dataTypes->where('slug', $permission->table_name)->first();

            if ($dataType && $dataType->slug) {
                $permission->key = str_replace($dataType->slug, $dataType->name, $permission->key);
                $permission->table_name = $dataType->name;
                $permission->save();
            }
        }
    }
}
