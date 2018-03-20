<?php

namespace LaravelAdminPanel\Listeners;

use LaravelAdminPanel\Events\/*CrudDataAdded*/CrudDataChanged;
use LaravelAdminPanel\Facades\Admin;

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
     * Create a MenuItem for a given BREAD.
     *
     * @param CrudAdded $event
     *
     * @return void
     */
    public function handle(/*CrudDataAdded*/ CrudDataChanged $crudData)
    {
        \Log::info('CrudDataChanged', [$crudData]);
        dd($crudData);
    }
}
