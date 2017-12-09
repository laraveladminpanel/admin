<?php

namespace LaravelAdminPanel\Events;

use Illuminate\Queue\SerializesModels;
use LaravelAdminPanel\Models\Menu;

class MenuDisplay
{
    use SerializesModels;

    public $menu;

    public function __construct(Menu $menu)
    {
        $this->menu = $menu;

        // @deprecate
        //
        event('admin.menu.display', $menu);
    }
}
