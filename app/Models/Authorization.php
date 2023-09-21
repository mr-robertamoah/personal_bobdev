<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Authorization extends Model
{
    use HasFactory;

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function authorizable()
    {
        return $this->morphTo();
    }

    public function authorized()
    {
        return $this->morphTo();
    }

    public function scopeWhereAuthorizable($query, Model $authorizable)
    {
        return $query->where(function ($q) use ($authorizable) {
            $q->where("authorizable_type", $authorizable::class)
            ->where("authorizable_id", $authorizable->id);
        });
    }

    public function scopeWhereAuthorized($query, ?Model $authorized = null)
    {
        return $query->where(function ($query) use ($authorized) {
            $query->where("authorized_type", $authorized::class)
                ->where("authorized_id", $authorized->id);
        });
    }

    public function scopeWhereAuthorizedHasPermissionName($query, string $name)
    {
        return $query->whereMorph("authorized", function ($query, $type) use ($name) {
            if ($type == "App\\Models\\Permission")
            {
                $query->where("name", $name);
                return;
            }

            $query->whereHas("permissions", function ($query) use ($name) {
                $query->where("name", $name);
            });
        });
    }

    public function scopeWhereAuthorizedHasPermissionId($query, string $id)
    {
        return $query->whereMorph("authorized", function ($query, $type) use ($id) {
            if ($type == "App\\Models\\Permission")
            {
                $query->whereId($id);
                return;
            }

            $query->whereHas("permissions", function ($query) use ($id) {
                $query->whereId($id);
            });
        });
    }
}
