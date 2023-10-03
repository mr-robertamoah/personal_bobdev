<?php

namespace App\Models;

use App\Traits\HasAuthorizationTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    use HasFactory;
    use HasAuthorizationTrait;

    const AUTHORIZEDCLASSES = [
        Company::class, Project::class
    ];

    protected $fillable = [
        'name', 'class', 'description', 'public'
    ];

    public function admin()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function roles()
    {
        return $this->belongsToMany(Role::class, 'permission_role');
    }

    public function users()
    {
        return $this->morphToMany(User::class, "authorized", "authorizations");
    }

    // scopes
    
}
