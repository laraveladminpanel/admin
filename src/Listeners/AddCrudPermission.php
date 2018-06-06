<?php

namespace LaravelAdminPanel\Listeners;

use LaravelAdminPanel\Events\CrudAdded;
use LaravelAdminPanel\Facades\Admin;
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
     * Create Permission for a given CRUD.
     *
     * @param CrudAdded $event
     *
     * @return void
     */
    public function handle(CrudAdded $crud)
    {
        if (config('admin.add_crud_permission') && file_exists(base_path('routes/web.php'))) {
            // Create permission
            //
            // Permission::generateFor(snake_case($crud->dataType->slug));
            $role = Role::where('name', 'admin')->firstOrFail();

            // Get permission for added table
            $permissions = Permission::where(['slug' => $crud->dataType->slug])->get()->pluck('id')->all();

            // Assign permission to admin
            $role->permissions()->attach($permissions);
        }
    }
}
