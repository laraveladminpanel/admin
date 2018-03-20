<?php

namespace LaravelAdminPanel\Models;

use Illuminate\Database\Eloquent\Model;

class UserRecord extends Model
{
    protected $table = 'user_records';
    protected $fillable = ['user_id', 'table_name', 'record_id'];
}
