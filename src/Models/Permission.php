<?php

namespace LaravelAdminPanel\Models;

use Illuminate\Database\Eloquent\Model;
use LaravelAdminPanel\Traits\HasRelationships;

class Permission extends Model
{
    use HasRelationships;

    protected $guarded = [];

    public function roles()
    {
        return $this->hasMany(Role::class);
    }

    public static function generateFor($slug)
    {
        self::firstOrCreate(['key' => 'browse_'.$slug, 'slug' => $slug]);
        self::firstOrCreate(['key' => 'read_'.$slug, 'slug' => $slug]);
        self::firstOrCreate(['key' => 'edit_'.$slug, 'slug' => $slug]);
        self::firstOrCreate(['key' => 'add_'.$slug, 'slug' => $slug]);
        self::firstOrCreate(['key' => 'delete_'.$slug, 'slug' => $slug]);
    }

    public static function removeFrom($slug)
    {
        self::where(['slug' => $slug])->delete();
    }
}
