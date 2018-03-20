<?php

namespace LaravelAdminPanel\Listeners;

use Illuminate\Support\Facades\Auth;
use LaravelAdminPanel\Events\CrudDataDeleted;
use LaravelAdminPanel\Models\User;

class DeleteUserRecord
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
     * Delete a user record for a given CRUD.
     *
     * @param CrudDataDeleted $event
     *
     * @return void
     */
    public function handle(CrudDataDeleted $crudData)
    {
        $user = User::find(Auth::id());

        $user->records()->whereTableName($crudData->dataType->name)
            ->whereIn('record_id', $crudData->ids)->delete();
    }
}
