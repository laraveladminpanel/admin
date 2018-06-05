<?php

use LaravelAdminPanel\Events\Routing;
use LaravelAdminPanel\Events\RoutingAdmin;
use LaravelAdminPanel\Models\DataType;

/*
|--------------------------------------------------------------------------
|  Routes
|--------------------------------------------------------------------------
|
| This file is where you may override any of the routes that are included
| with .
|
*/

Route::group(['as' => 'admin.'], function () {
    event(new Routing());

    $namespacePrefix = '\\'.config('admin.controllers.namespace').'\\';

    Route::get('login', ['uses' => $namespacePrefix.'AuthController@login',     'as' => 'login']);
    Route::post('login', ['uses' => $namespacePrefix.'AuthController@postLogin', 'as' => 'postlogin']);

    Route::group(['middleware' => 'admin.user'], function () use ($namespacePrefix) {
        event(new RoutingAdmin());

        // Main Admin and Logout Route
        Route::get('/', ['uses' => $namespacePrefix.'Controller@index',   'as' => 'dashboard']);
        Route::post('logout', ['uses' => $namespacePrefix.'Controller@logout',  'as' => 'logout']);
        Route::post('upload', ['uses' => $namespacePrefix.'Controller@upload',  'as' => 'upload']);

        Route::get('profile', ['uses' => $namespacePrefix.'Controller@profile', 'as' => 'profile']);

        try {
            foreach (DataType::all() as $dataType) {
                $crudController = $dataType->controller
                                 ? $dataType->controller
                                 : $namespacePrefix.'CrudController';

                Route::resource($dataType->slug, $crudController);
            }
        } catch (\InvalidArgumentException $e) {
            throw new \InvalidArgumentException("Custom routes hasn't been configured because: ".$e->getMessage(), 1);
        } catch (\Exception $e) {
            // do nothing, might just be because table not yet migrated.
        }

        Route::post('get-ajax-list', ['uses' => $namespacePrefix . 'CrudController@getAjaxList', 'as' => 'get-ajax-list']);

        // Role Routes
        Route::resource('roles', $namespacePrefix.'RoleController');

        // Menu Routes
        Route::group([
            'as'     => 'menus.',
            'prefix' => 'menus/{menu}',
        ], function () use ($namespacePrefix) {
            Route::get('builder', ['uses' => $namespacePrefix.'MenuController@builder',    'as' => 'builder']);
            Route::post('order', ['uses' => $namespacePrefix.'MenuController@order_item', 'as' => 'order']);

            Route::group([
                'as'     => 'item.',
                'prefix' => 'item',
            ], function () use ($namespacePrefix) {
                Route::delete('{id}', ['uses' => $namespacePrefix.'MenuController@delete_menu', 'as' => 'destroy']);
                Route::post('/', ['uses' => $namespacePrefix.'MenuController@add_item',    'as' => 'add']);
                Route::put('/', ['uses' => $namespacePrefix.'MenuController@update_item', 'as' => 'update']);
            });
        });

        // Settings
        Route::group([
            'as'     => 'settings.',
            'prefix' => 'settings',
        ], function () use ($namespacePrefix) {
            Route::get('/', ['uses' => $namespacePrefix.'SettingsController@index',        'as' => 'index']);
            Route::post('/', ['uses' => $namespacePrefix.'SettingsController@store',        'as' => 'store']);
            Route::put('/', ['uses' => $namespacePrefix.'SettingsController@update',       'as' => 'update']);
            Route::delete('{id}', ['uses' => $namespacePrefix.'SettingsController@delete',       'as' => 'delete']);
            Route::get('{id}/move_up', ['uses' => $namespacePrefix.'SettingsController@move_up',      'as' => 'move_up']);
            Route::get('{id}/move_down', ['uses' => $namespacePrefix.'SettingsController@move_down',    'as' => 'move_down']);
            Route::get('{id}/delete_value', ['uses' => $namespacePrefix.'SettingsController@delete_value', 'as' => 'delete_value']);
        });

        // Admin Media
        Route::group([
            'as'     => 'media.',
            'prefix' => 'media',
        ], function () use ($namespacePrefix) {
            Route::get('/', ['uses' => $namespacePrefix.'MediaController@index',              'as' => 'index']);
            Route::post('files', ['uses' => $namespacePrefix.'MediaController@files',              'as' => 'files']);
            Route::post('new_folder', ['uses' => $namespacePrefix.'MediaController@new_folder',         'as' => 'new_folder']);
            Route::post('delete_file_folder', ['uses' => $namespacePrefix.'MediaController@delete_file_folder', 'as' => 'delete_file_folder']);
            Route::post('directories', ['uses' => $namespacePrefix.'MediaController@get_all_dirs',       'as' => 'get_all_dirs']);
            Route::post('move_file', ['uses' => $namespacePrefix.'MediaController@move_file',          'as' => 'move_file']);
            Route::post('rename_file', ['uses' => $namespacePrefix.'MediaController@rename_file',        'as' => 'rename_file']);
            Route::post('upload', ['uses' => $namespacePrefix.'MediaController@upload',             'as' => 'upload']);
            Route::post('remove', ['uses' => $namespacePrefix.'MediaController@remove',             'as' => 'remove']);
            Route::post('crop', ['uses' => $namespacePrefix.'MediaController@crop',             'as' => 'crop']);
        });

        // Database Routes
        Route::group([
            'as'     => 'database.crud.',
            'prefix' => 'database',
        ], function () use ($namespacePrefix) {
            Route::get('{table}/crud/create', ['uses' => $namespacePrefix.'DatabaseCrudController@add',     'as' => 'create']);
            Route::post('crud', ['uses' => $namespacePrefix.'DatabaseCrudController@store',   'as' => 'store']);
            Route::get('{slug}/crud/edit', ['uses' => $namespacePrefix.'DatabaseCrudController@edit', 'as' => 'edit']);
            Route::put('crud/{id}', ['uses' => $namespacePrefix.'DatabaseCrudController@update',  'as' => 'update']);
            Route::get('{slug}/crud/clone', ['uses' => $namespacePrefix.'DatabaseCrudController@clone', 'as' => 'clone']);
            Route::delete('crud/{id}', ['uses' => $namespacePrefix.'DatabaseCrudController@delete',  'as' => 'delete']);
            Route::post('crud/relationship', ['uses' => $namespacePrefix.'DatabaseCrudController@addRelationship',  'as' => 'relationship']);
            Route::get('crud/delete_relationship/{id}', ['uses' => $namespacePrefix.'DatabaseCrudController@deleteRelationship',  'as' => 'delete_relationship']);
        });

        // Compass Routes
        Route::group([
            'as'     => 'compass.',
            'prefix' => 'compass',
        ], function () use ($namespacePrefix) {
            Route::get('/', ['uses' => $namespacePrefix.'CompassController@index',  'as' => 'index']);
            Route::post('/', ['uses' => $namespacePrefix.'CompassController@index',  'as' => 'post']);
        });

        Route::resource('database', $namespacePrefix.'DatabaseController');

        // Apt Routes
        Route::group([
            'as'     => 'api.',
            'prefix' => 'api',
        ], function () use ($namespacePrefix) {
            Route::post('/order', [
                'uses' => $namespacePrefix.'ApiController@order',
                'as' => 'order'
            ]);
        });
    });
});
