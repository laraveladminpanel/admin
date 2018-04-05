<?php

namespace LaravelAdminPanel\Models;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use LaravelAdminPanel\Contracts\User as UserContract;
use LaravelAdminPanel\Facades\Admin;
use LaravelAdminPanel\Traits\AdminUser;

class User extends Authenticatable implements UserContract
{
    use AdminUser;

    protected $guarded = [];

    public function getAvatarAttribute($value)
    {
        if (is_null($value)) {
            return config('admin.user.default_avatar', 'users/default.png');
        }

        return $value;
    }

    public function setCreatedAtAttribute($value)
    {
        $this->attributes['created_at'] = Carbon::parse($value)->format('Y-m-d H:i:s');
    }

    public function save(array $options = [])
    {
        if (auth()->user() && (int) $this->id === auth()->user()->id) {
            $this->role_id = auth()->user()->role_id;
        }

        parent::save();
    }
}
