<?php

if (!function_exists('setting')) {
    function setting($key, $default = null)
    {
        return LaravelAdminPanel\Facades\Admin::setting($key, $default);
    }
}

if (!function_exists('menu')) {
    function menu($menuName, $type = null, array $options = [])
    {
        return LaravelAdminPanel\Models\Menu::display($menuName, $type, $options);
    }
}

if (!function_exists('admin_asset')) {
    function admin_asset($path, $secure = null)
    {
        return asset(config('admin.assets_path').'/'.$path, $secure);
    }
}
