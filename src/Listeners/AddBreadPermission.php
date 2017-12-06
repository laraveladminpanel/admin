<?php

namespace LaravelAdminPanel\Listeners;

use LaravelAdminPanel\Events\CrudAdded;
use LaravelAdminPanel\Facades\Voyager;
use LaravelAdminPanel\Models\Permission;
use LaravelAdminPanel\Models\Role;

class AddCrudPermission
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Create Permission for a given BREAD.
     *
     * @param CrudAdded $event
     *
     * @return void
     */
    public function handle(CrudAdded $crud)
    {
        if (config('voyager.add_crud_permission') && file_exists(base_path('routes/web.php'))) {
            // Create permission
            //
            // Permission::generateFor(snake_case($crud->dataType->slug));
            $role = Role::where('name', 'admin')->firstOrFail();

            // Get permission for added table
            $permissions = Permission::where(['table_name' => $crud->dataType->name])->get()->pluck('id')->all();

            // Assign permission to admin
            $role->permissions()->attach($permissions);
        }
    }
}
