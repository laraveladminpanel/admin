<?php

namespace LaravelAdminPanel\Listeners;

use Illuminate\Support\Facades\Auth;
use LaravelAdminPanel\Events\CrudDataAdded;
use LaravelAdminPanel\Facades\Admin;
use LaravelAdminPanel\Models\User;

class AddUserRecord
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
     * Create a MenuItem for a given CRUD.
     *
     * @param CrudAdded $event
     *
     * @return void
     */
    public function handle(CrudDataAdded $crudData)
    {
        $userRecord = [
            'table_name' => $crudData->dataType->name,
            'record_id' => $crudData->data->id,
        ];

        $user = User::find(Auth::id());
        $user->records()->firstOrCreate($userRecord);
    }
}
