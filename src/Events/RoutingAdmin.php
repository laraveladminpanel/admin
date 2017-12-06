<?php

namespace LaravelAdminPanel\Events;

use Illuminate\Queue\SerializesModels;

class RoutingAdmin
{
    use SerializesModels;

    public $router;

    public function __construct()
    {
        $this->router = app('router');

        // @deprecate
        //
        event('admin.admin.routing', $this->router);
    }
}
