<?php

namespace LaravelAdminPanel\Tests;

use Illuminate\Support\Facades\Auth;

class DashboardTest extends TestCase
{
    protected $withDummy = true;

    public function setUp()
    {
        parent::setUp();

        $this->install();
    }

    public function testWeHaveAccessToTheMainSections()
    {
        // We must first login and visit the dashboard page.
        Auth::loginUsingId(1);

        $this->visit(route('admin.dashboard'));

        $this->see(__('admin.generic.dashboard'));

        // We can see number of Users.
        $this->see(trans_choice('admin.dimmer.user', 1));

        // list them.
        $this->click(__('admin.dimmer.user_link_text'));
        $this->seePageIs(route('admin.users.index'));

        // and return to dashboard from there.
        $this->click(__('admin.generic.dashboard'));
        $this->seePageIs(route('admin.dashboard'));

        // We can see number of posts.
        $this->see(trans_choice('admin.dimmer.post', 4));

        // list them.
        $this->click(__('admin.dimmer.post_link_text'));
        $this->seePageIs(route('admin.posts.index'));

        // and return to dashboard from there.
        $this->click(__('admin.generic.dashboard'));
        $this->seePageIs(route('admin.dashboard'));

        // We can see number of Pages.
        $this->see(trans_choice('admin.dimmer.page', 1));

        // list them.
        $this->click(__('admin.dimmer.page_link_text'));
        $this->seePageIs(route('admin.pages.index'));

        // and return to Dashboard from there.
        $this->click(__('admin.generic.dashboard'));
        $this->seePageIs(route('admin.dashboard'));
        $this->see(__('admin.generic.dashboard'));
    }
}
