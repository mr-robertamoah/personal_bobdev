<?php

namespace App\Models;

use App\Traits\HasAuthorizationTrait;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;
    use HasAuthorizationTrait;

    protected $fillable = [
        'name', 'class', 'description', 'public'
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

    // scopes
    public function scopeWherePermissionName($query, string $name)
    {
        return $query->where(function ($q) use ($name) {
            $q->whereHas("permissions", function ($q) use ($name) {
                $q->where("name", $name);
            });
        });
    }
    
    public function scopeWherePermissionNames($query, string $names)
    {
        return $query->where(function ($q) use ($names) {
            $q->whereHas("permissions", function ($q) use ($names) {
                $q->whereIn("name", $names);
            });
        });
    }

    public function scopeWhereIsLike($query, string $like)
    {
        return $query->where(function ($q) use ($like) {
            $q->where("name", "LIKE", "%{$like}%");
        })->orWhere(function ($q) use ($like) {
            $q->where("description", "LIKE", "%{$like}%");
        })->orWhereHas("permissions", function ($q) use ($like) {
            $q->where(function ($q) use ($like) {
                $q->where("name", "LIKE", "%{$like}%");
            })
            ->orWhere(function ($q) use ($like) {
                $q->where("description", "LIKE", "%{$like}%");
            });
        });
    }
}
