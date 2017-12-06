<?php

namespace LaravelAdminPanel\Tests;

class LoginTest extends TestCase
{
    public function setUp()
    {
        parent::setUp();

        $this->install();
    }

    public function testSuccessfulLoginWithDefaultCredentials()
    {
        $this->visit(route('admin.login'));
        $this->type('admin@admin.com', 'email');
        $this->type('password', 'password');
        $this->press(__('admin.generic.login'));
        $this->seePageIs(route('admin.dashboard'));
    }

    public function testShowAnErrorMessageWhenITryToLoginWithWrongCredentials()
    {
        $this->visit(route('admin.login'))
             ->type('john@Doe.com', 'email')
             ->type('pass', 'password')
             ->press(__('admin.generic.login'))
             ->seePageIs(route('admin.login'))
             ->see(__('auth.failed'))
             ->seeInField('email', 'john@Doe.com');
    }
}
