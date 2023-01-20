<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Relation extends Model
{
    use HasFactory;

    protected $fillable = [
        'relationship_type'
    ];

    public function by()
    {
        return $this->morphTo();
    }

    public function to()
    {
        return $this->morphTo();
    }

    public function scopeWhereUserIsInARelationshipType($query, $user, $type)
    {
        return $query
            ->where('relationship_type', $type)
            ->where(function($query) use ($user) {
                $query
                    ->where('to_type', $user::class)
                    ->where('to_id', $user->id);
            })
            ->orWhere(function($query) use ($user) {
                $query
                    ->where('by_type', $user::class)
                    ->where('by_id', $user->id);
            });
    }

    public function scopeWhereBy($query, $model)
    {
        return $query
            ->where('by_type', $model::class)
            ->where('by_id', $model->id);
    }

    public function scopeWhereTo($query, $model)
    {
        return $query
            ->where('to_type', $model::class)
            ->where('to_id', $model->id);
    }

    public function scopeWhereType($query, $type)
    {
        return $query
            ->where('relationship_type', $type);
    }
}
