<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Authorization extends Model
{
    use HasFactory;

    protected $fillable = ["user_id"];

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

    public function authorization()
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

    public function scopeWhereAuthorizationName($query, string $name)
    {
        return $query->whereHasMorph("authorization", "*", function ($query, $type) use ($name) {
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

    public function scopeWhereAuthorizationNames($query, array $names = [])
    {
        return $query->whereHasMorph("authorization", "*", function ($query, $type) use ($names) {
            if ($type == "App\\Models\\Permission")
            {
                $query->whereIn("name", $names);
                return;
            }

            $query->whereHas("permissions", function ($query) use ($names) {
                $query->whereIn("name", $names);
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

    public function scopeWhereAuthorization($query, $authorizaton)
    {
        return $query->where(function($q) use ($authorizaton){
            $q->where('authorization_type', $authorizaton::class)
                ->where('authorization_id', $authorizaton->id);
        });
    }
}
