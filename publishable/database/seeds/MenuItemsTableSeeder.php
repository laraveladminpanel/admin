<?php

use Illuminate\Database\Seeder;
use LaravelAdminPanel\Models\Menu;
use LaravelAdminPanel\Models\MenuItem;

class MenuItemsTableSeeder extends Seeder
{
    /**
     * Auto generated seed file.
     *
     * @return void
     */
    public function run()
    {
        if (file_exists(base_path('routes/web.php'))) {
            require base_path('routes/web.php');

            $menu = Menu::where('name', 'admin')->firstOrFail();

            $menuItem = MenuItem::firstOrNew([
                'menu_id' => $menu->id,
                'title'   => 'Dashboard',
                'url'     => '',
                'route'   => 'admin.dashboard',
            ]);
            if (!$menuItem->exists) {
                $menuItem->fill([
                    'target'     => '_self',
                    'icon_class' => 'admin-boat',
                    'color'      => null,
                    'parent_id'  => null,
                    'order'      => 1,
                ])->save();
            }

            $menuItem = MenuItem::firstOrNew([
                'menu_id' => $menu->id,
                'title'   => 'Media',
                'url'     => '',
                'route'   => 'admin.media.index',
            ]);
            if (!$menuItem->exists) {
                $menuItem->fill([
                    'target'     => '_self',
                    'icon_class' => 'admin-images',
                    'color'      => null,
                    'parent_id'  => null,
                    'order'      => 5,
                ])->save();
            }

            $menuItem = MenuItem::firstOrNew([
                'menu_id' => $menu->id,
                'title'   => 'Posts',
                'url'     => '',
                'route'   => 'admin.posts.index',
            ]);
            if (!$menuItem->exists) {
                $menuItem->fill([
                    'target'     => '_self',
                    'icon_class' => 'admin-news',
                    'color'      => null,
                    'parent_id'  => null,
                    'order'      => 6,
                ])->save();
            }

            $menuItem = MenuItem::firstOrNew([
                'menu_id' => $menu->id,
                'title'   => 'Users',
                'url'     => '',
                'route'   => 'admin.users.index',
            ]);
            if (!$menuItem->exists) {
                $menuItem->fill([
                    'target'     => '_self',
                    'icon_class' => 'admin-person',
                    'color'      => null,
                    'parent_id'  => null,
                    'order'      => 3,
                ])->save();
            }

            $menuItem = MenuItem::firstOrNew([
                'menu_id' => $menu->id,
                'title'   => 'Categories',
                'url'     => '',
                'route'   => 'admin.categories.index',
            ]);
            if (!$menuItem->exists) {
                $menuItem->fill([
                    'target'     => '_self',
                    'icon_class' => 'admin-categories',
                    'color'      => null,
                    'parent_id'  => null,
                    'order'      => 8,
                ])->save();
            }

            $menuItem = MenuItem::firstOrNew([
                'menu_id' => $menu->id,
                'title'   => 'Pages',
                'url'     => '',
                'route'   => 'admin.pages.index',
            ]);
            if (!$menuItem->exists) {
                $menuItem->fill([
                    'target'     => '_self',
                    'icon_class' => 'admin-file-text',
                    'color'      => null,
                    'parent_id'  => null,
                    'order'      => 7,
                ])->save();
            }

            $menuItem = MenuItem::firstOrNew([
                'menu_id' => $menu->id,
                'title'   => 'Roles',
                'url'     => '',
                'route'   => 'admin.roles.index',
            ]);
            if (!$menuItem->exists) {
                $menuItem->fill([
                    'target'     => '_self',
                    'icon_class' => 'admin-lock',
                    'color'      => null,
                    'parent_id'  => null,
                    'order'      => 2,
                ])->save();
            }

            $toolsMenuItem = MenuItem::firstOrNew([
                'menu_id' => $menu->id,
                'title'   => 'Tools',
                'url'     => '',
            ]);
            if (!$toolsMenuItem->exists) {
                $toolsMenuItem->fill([
                    'target'     => '_self',
                    'icon_class' => 'admin-tools',
                    'color'      => null,
                    'parent_id'  => null,
                    'order'      => 9,
                ])->save();
            }

            $menuItem = MenuItem::firstOrNew([
                'menu_id' => $menu->id,
                'title'   => 'Menu Builder',
                'url'     => '',
                'route'   => 'admin.menus.index',
            ]);
            if (!$menuItem->exists) {
                $menuItem->fill([
                    'target'     => '_self',
                    'icon_class' => 'admin-list',
                    'color'      => null,
                    'parent_id'  => $toolsMenuItem->id,
                    'order'      => 10,
                ])->save();
            }

            $menuItem = MenuItem::firstOrNew([
                'menu_id' => $menu->id,
                'title'   => 'Database',
                'url'     => '',
                'route'   => 'admin.database.index',
            ]);
            if (!$menuItem->exists) {
                $menuItem->fill([
                    'target'     => '_self',
                    'icon_class' => 'admin-data',
                    'color'      => null,
                    'parent_id'  => $toolsMenuItem->id,
                    'order'      => 11,
                ])->save();
            }

            $menuItem = MenuItem::firstOrNew([
                'menu_id' => $menu->id,
                'title'   => 'Compass',
                'url'     => route('admin.compass.index', [], false),
            ]);
            if (!$menuItem->exists) {
                $menuItem->fill([
                    'target'     => '_self',
                    'icon_class' => 'admin-compass',
                    'color'      => null,
                    'parent_id'  => $toolsMenuItem->id,
                    'order'      => 12,
                ])->save();
            }

            $menuItem = MenuItem::firstOrNew([
                'menu_id' => $menu->id,
                'title'   => 'Settings',
                'url'     => '',
                'route'   => 'admin.settings.index',
            ]);
            if (!$menuItem->exists) {
                $menuItem->fill([
                    'target'     => '_self',
                    'icon_class' => 'admin-settings',
                    'color'      => null,
                    'parent_id'  => null,
                    'order'      => 14,
                ])->save();
            }
        }
    }
}
