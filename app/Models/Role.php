<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    protected $table = 'roles';
    protected $fillable = [
        'slug',
        'description',
    ];

    public function userRoles()
    {
        return $this->hasMany(UserRole::class);
    }
    public function users()
    {
        return $this->belongsToMany(User::class, 'user_roles');
    }
}
