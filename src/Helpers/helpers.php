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

if (!function_exists('asset_with_time')) {
    function asset_with_time($path, $secure = null)
    {
        $file = public_path($path);

        if (file_exists($file)) {
            return asset($path, $secure) . '?' . filemtime($file);
        }

        return asset($path, $secure);
    }
}

if (!function_exists('storage_url')) {
    function storage_url($path)
    {
        return Storage::disk(config('admin.storage.disk'))->url($path);
    }
}
