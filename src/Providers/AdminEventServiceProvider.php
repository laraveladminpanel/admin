<?php

namespace LaravelAdminPanel\Providers;

use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class AdminEventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        'LaravelAdminPanel\Events\CrudAdded' => [
            'LaravelAdminPanel\Listeners\AddCrudMenuItem',
            'LaravelAdminPanel\Listeners\AddCrudPermission',
        ],
        'LaravelAdminPanel\Events\CrudDataAdded' => [
            'LaravelAdminPanel\Listeners\CropImages',
        ],
        'LaravelAdminPanel\Events\CrudDataUpdated' => [
            'LaravelAdminPanel\Listeners\CropImages',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();
    }
}
