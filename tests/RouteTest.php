<?php

namespace LaravelAdminPanel\Tests;

class RouteTest extends TestCase
{
    protected $withDummy = true;

    public function setUp()
    {
        parent::setUp();

        $this->install();
    }

    /**
     * A basic functional test example.
     *
     * @return void
     */
    public function testGetRoutes()
    {
        $this->disableExceptionHandling();

        $this->visit(route('admin.login'));
        $this->type('admin@admin.com', 'email');
        $this->type('password', 'password');
        $this->press(__('admin.generic.login'));

        $urls = [
            route('admin.dashboard'),
            route('admin.media.index'),
            route('admin.settings.index'),
            route('admin.roles.index'),
            route('admin.roles.create'),
            route('admin.roles.show', ['role' => 1]),
            route('admin.roles.edit', ['role' => 1]),
            route('admin.users.index'),
            route('admin.users.create'),
            route('admin.users.show', ['user' => 1]),
            route('admin.users.edit', ['user' => 1]),
            route('admin.posts.index'),
            route('admin.posts.create'),
            route('admin.posts.show', ['post' => 1]),
            route('admin.posts.edit', ['post' => 1]),
            route('admin.pages.index'),
            route('admin.pages.create'),
            route('admin.pages.show', ['page' => 1]),
            route('admin.pages.edit', ['page' => 1]),
            route('admin.categories.index'),
            route('admin.categories.create'),
            route('admin.categories.show', ['category' => 1]),
            route('admin.categories.edit', ['category' => 1]),
            route('admin.menus.index'),
            route('admin.menus.create'),
            route('admin.menus.show', ['menu' => 1]),
            route('admin.menus.edit', ['menu' => 1]),
            route('admin.database.index'),
            route('admin.database.crud.edit', ['table' => 'categories']),
            route('admin.database.edit', ['table' => 'categories']),
            route('admin.database.create'),
        ];

        foreach ($urls as $url) {
            $response = $this->call('GET', $url);
            $this->assertEquals(200, $response->status(), $url.' did not return a 200');
        }
    }
}
