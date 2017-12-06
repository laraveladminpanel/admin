<?php

namespace LaravelAdminPanel\Tests;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use LaravelAdminPanel\Models\Role;

class UserProfileTest extends TestCase
{
    use DatabaseTransactions;

    protected $user;

    protected $editPageForTheCurrentUser;

    protected $listOfUsers;

    protected $withDummy = true;

    public function setUp()
    {
        parent::setUp();

        $this->install();

        $this->user = Auth::loginUsingId(1);

        $this->editPageForTheCurrentUser = route('admin.users.edit', ['user' => $this->user->id]);

        $this->listOfUsers = route('admin.users.index');

        $this->withFactories(__DIR__.'/database/factories');
    }

    public function testCanSeeTheUserInfoOnHisProfilePage()
    {
        $this->visit(route('admin.profile'))
             ->seeInElement('h4', $this->user->name)
             ->seeInElement('.user-email', $this->user->email)
             ->seeLink(__('admin.profile.edit'));
    }

    public function testCanEditUserName()
    {
        $this->visit(route('admin.profile'))
             ->click(__('admin.profile.edit'))
             ->see(__('admin.profile.edit_user'))
             ->seePageIs($this->editPageForTheCurrentUser)
             ->type('New Awesome Name', 'name')
             ->press(__('admin.generic.save'))
             ->seePageIs($this->listOfUsers)
             ->seeInDatabase(
                 'users',
                 ['name' => 'New Awesome Name']
             );
    }

    public function testCanEditUserEmail()
    {
        $this->visit(route('admin.profile'))
             ->click(__('admin.profile.edit'))
             ->see(__('admin.profile.edit_user'))
             ->seePageIs($this->editPageForTheCurrentUser)
             ->type('another@email.com', 'email')
             ->press(__('admin.generic.save'))
             ->seePageIs($this->listOfUsers)
             ->seeInDatabase(
                 'users',
                 ['email' => 'another@email.com']
             );
    }

    public function testCanEditUserPassword()
    {
        $this->visit(route('admin.profile'))
             ->click(__('admin.profile.edit'))
             ->see(__('admin.profile.edit_user'))
             ->seePageIs($this->editPageForTheCurrentUser)
             ->type('admin-rocks', 'password')
             ->press(__('admin.generic.save'))
             ->seePageIs($this->listOfUsers);

        $updatedPassword = DB::table('users')->where('id', 1)->first()->password;
        $this->assertTrue(Hash::check('admin-rocks', $updatedPassword));
    }

    public function testCanEditUserAvatar()
    {
        $this->visit(route('admin.profile'))
             ->click(__('admin.profile.edit'))
             ->see(__('admin.profile.edit_user'))
             ->seePageIs($this->editPageForTheCurrentUser)
             ->attach($this->newImagePath(), 'avatar')
             ->press(__('admin.generic.save'))
             ->seePageIs($this->listOfUsers)
             ->dontSeeInDatabase(
                 'users',
                 ['id' => 1, 'avatar' => 'user/default.png']
             );
    }

    public function testCanEditUserEmailWithEditorPermissions()
    {
        $user = factory(\LaravelAdminPanel\Models\User::class)->create();
        $editPageForTheCurrentUser = route('admin.users.edit', ['user' => $user->id]);
        $roleId = $user->role_id;
        $role = Role::find($roleId);
        // add permissions which reflect a possible editor role
        // without permissions to edit  users
        $role->permissions()->attach(\LaravelAdminPanel\Models\Permission::whereIn('key', [
            'browse_admin',
            'browse_users',
        ])->get()->pluck('id')->all());
        Auth::onceUsingId($user->id);
        $this->visit(route('admin.profile'))
             ->click(__('admin.profile.edit'))
             ->see(__('admin.profile.edit_user'))
             ->seePageIs($editPageForTheCurrentUser)
             ->type('another@email.com', 'email')
             ->press(__('admin.generic.save'))
             ->seePageIs($this->listOfUsers)
             ->seeInDatabase(
                 'users',
                 ['email' => 'another@email.com']
             );
    }

    protected function newImagePath()
    {
        return realpath(__DIR__.'/temp/new_avatar.png');
    }
}
