<?php

namespace LaravelAdminPanel\Listeners;

use LaravelAdminPanel\Events\CrudAdded;
use LaravelAdminPanel\Facades\Voyager;
use LaravelAdminPanel\Models\Menu;
use LaravelAdminPanel\Models\MenuItem;

class AddCrudMenuItem
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
    public function handle(CrudAdded $crud)
    {
        if (config('voyager.add_crud_menu_item') && file_exists(base_path('routes/web.php'))) {
            require base_path('routes/web.php');

            $menu = Menu::where('name', 'admin')->firstOrFail();

            $menuItem = MenuItem::firstOrNew([
                'menu_id' => $menu->id,
                'title'   => $crud->dataType->display_name_plural,
                'url'     => '/'.config('voyager.prefix', 'admin').'/'.$crud->dataType->slug,
            ]);

            $order = Voyager::model('MenuItem')->highestOrderMenuItem();

            if (!$menuItem->exists) {
                $menuItem->fill([
                    'target'     => '_self',
                    'icon_class' => $crud->dataType->icon,
                    'color'      => null,
                    'parent_id'  => null,
                    'order'      => $order,
                ])->save();
            }
        }
    }
}
