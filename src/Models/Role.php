<?php

namespace LaravelAdminPanel\Models;

use Illuminate\Database\Eloquent\Model;
use LaravelAdminPanel\Facades\Admin;

class Role extends Model
{
    protected $guarded = [];

    public function users()
    {
        return $this->belongsToMany(Admin::modelClass('User'), 'user_roles');
    }

    public function permissions()
    {
        return $this->belongsToMany(Admin::modelClass('Permission'));
    }
}
