<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    // TODO to be created by owners of companies and projects
    // assignable to members/participants
    // create facilitator, learners, owner, members, administrator roles
    // which are added by default

    protected $fillable = [
        'name', 'class', 'description'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_role');
    }

    public function users()
    {
        return $this->morphToMany(User::class, "authorized", "authorizations");
    }
}
