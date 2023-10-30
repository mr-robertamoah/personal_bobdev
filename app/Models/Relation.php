<?php

namespace App\Models;

use App\Enums\RelationshipTypeEnum;
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
            ->whereRelationshipType($type)
            ->whereIsTo($user)
            ->orWhere(function($query) use ($user) {
                $query->whereIsBy($user);
            });
    }

    public function scopeWhereIsRelated($query, $model)
    {
        return $query
            ->whereIsTo($model)
            ->orWhere(function($query) use ($model) {
                $query->whereIsBy($model);
            });
    }

    public function scopeWhereIsBy($query, $model)
    {
        return $query
        ->where(function ($q) use ($model) {
            $q->where('by_type', $model::class)
                ->where('by_id', $model->id);
        });
    }

    public function scopeWhereIsTo($query, $model)
    {
        return $query
        ->where(function ($q) use ($model) {
            $q->where('to_type', $model::class)
                ->where('to_id', $model->id);
        });
    }

    public function scopeWhereIsRelationshipType($query, $type)
    {
        $type = strtoupper($type);

        if ($type == "OFFICIAL")
            $type = RelationshipTypeEnum::companyAdministrator->value;

        return $query
            ->where(function ($q) use ($type) {
                $q->where('relationship_type', $type);
            });
    }

    public function scopeWhereOfficial($query)
    {
        return $query
            ->whereIsRelationshipType(RelationshipTypeEnum::companyAdministrator->value);
    }

    public function scopeWithAll($query)
    {
        return $query->with(["to", "by"]);
    }
}
