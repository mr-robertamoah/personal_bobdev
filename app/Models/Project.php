<?php

namespace App\Models;

use app\enums\ProjectParticipantEnum;
use App\Traits\HasRequestForTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Abstracts\Requestable;
use App\Traits\HasAuthorizableTrait;

class Project extends Requestable
{
    use HasFactory,
    HasRequestForTrait,
    SoftDeletes,
    HasAuthorizableTrait;

    protected $fillable = ['name', 'description', 'start_date', 'end_date'];

    public function owner(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes)=> $this->addedby
        );
    }

    public function startDate(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes)=> array_key_exists('start_date', $attributes) ? 
                $attributes['start_date'] : null
        );
    }

    public function endDate(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes)=> array_key_exists('end_date', $attributes) ? 
                $attributes['end_date'] : null
        );
    }

    public function facilitators(): Attribute
    {
        return Attribute::make(
            get: function($value, $attributes) {
                return $this->participants()
                    ->wherePivot('participating_as', ProjectParticipantEnum::facilitator->value)
                    ->get();
            }
        );
    }

    public function learners(): Attribute
    {
        return Attribute::make(
            get: fn($attributes)=> $this->participants()
                ->wherePivot('participating_as', ProjectParticipantEnum::learner->value)
                ->get()
        );
    }

    public function sponsors(): Attribute
    {
        return Attribute::make(
            get: fn($attributes)=> $this->participants()
                ->wherePivot('participating_as', ProjectParticipantEnum::sponsor->value)
                ->get()
        );
    }

    public function addedby()
    {
        return $this->morphTo();
    }

    public function projectSessions()
    {
        return $this->hasMany(ProjectSession::class);
    }

    public function skills()
    {
        return $this->belongsToMany(Skill::class)
            ->withTimestamps();
    }

    public function hasAnyOfSkills(array $skillIds) : bool
    {
        return count(array_intersect($this->skills()->allRelatedIds()->toArray(), $skillIds)) > 0;
    }

    public function doesNotHaveAnyOfSkills(array $skillIds) : bool
    {
        return count(array_intersect($this->skills()->allRelatedIds()->toArray(), $skillIds)) == 0;
    }

    public function isFacilitator(Model $model) : bool
    {
        return $this->isParticipantType($model, ProjectParticipantEnum::facilitator->value);
    }

    public function isNotFacilitator(Model $model) : bool
    {
        return !$this->isFacilitator($model, ProjectParticipantEnum::facilitator->value);
    }

    public function isLearner(Model $model)
    {
        return $this->isParticipantType($model, ProjectParticipantEnum::learner->value);
    }

    public function isStudent(Model $model)
    {
        return $this->isLearner($model);
    }

    public function isSponsor(Model $model)
    {
        return $this->isParticipantType($model, ProjectParticipantEnum::sponsor->value);
    }

    public function isParticipantType(Model $model, string $type)
    {
        return $this->participants()
            ->whereIsParticipant($model)
            ->whereParticipationType($type)
            ->exists();
    }

    public function participants()
    {
        return $this->hasMany(ProjectParticipant::class);
    }

    public function isParticipant($model): bool
    {
        if (is_null($model)) {
            return false;
        }
        
        return $this->participants()
            ->whereIsParticipant($model)
            ->exists();
    }

    public function isNotParticipant($model): bool
    {
        return !$this->isParticipant($model);
    }

    public function isOfficial(Model $model): bool
    {
        if (is_null($model)) {
            return false;
        }

        if ($this->isOwnedByCompany()) {
            return $this->addedby->isOfficial($model);
        }

        return $this
            ->whereAddedby($model)
            ->exists();
    }

    public function isOwnedByCompany()
    {
        return $this->addedby::class === Company::class;
    }

    public function getProjectParticipant(Model $model, string $type): ?ProjectParticipant
    {
        return $this->participants()
            ->whereIsParticipant($model)
            ->whereParticipationType($type)
            ->first();
    }

    public function scopeWhereAddedby($query, Model $model)
    {
        return $query->where(function ($q) use ($model) {
            $q->where("addedby_id", $model->id)
                ->where("addedby_type", $model::class);
        });
    }

    public function scopeWhereIsParticipant($query, Model $model)
    {
        return $query->where(function($query) use ($model) {
            $query->whereHas("participants", function ($query) use ($model) {
                $query->whereIsParticipant($model);
            });
        });
    }

    public function scopeWhereParticipationType($query, string $type)
    {
        return $query->where(function($query) use ($type) {
            $query->whereHas("participants", function ($query) use ($type) {
                $query->whereParticipationType($type);
            });
        });
    }
    
    public function scopeWhereIsOwnedBy($query, Model $model)
    {
        return $query->where(function ($q) use ($model) {
            $q->whereAddedby($model)
                ->orWhereHasMorph("addedby", "App\\Models\\Company", function ($q) use ($model) {
                    $q->whereIsOwnedBy($model);
                });
        });
    }
    
    public function scopeWhereIsOfficial($query, Model $model)
    {
        return $query->where(function ($q) use ($model) {
            $q->whereAddedby($model)
                ->orWhereHasMorph("addedby", [Company::class], function ($q) use ($model) {
                    $q->whereIsOfficial($model);
                });
        });
    }
    
    public function scopeWhereIsMember($query, Model $model)
    {
        return $query->where(function ($q) use ($model) {
            $q->orWhereHasMorph("addedby", [Company::class], function ($q) use ($model) {
                    $q->whereIsMember($model);
                });
        });
    }
    
    public function scopeWhereHasSkillWithNameLike($query, string $name)
    {
        return $query->where(function ($q) use ($name) {
            $q->whereHas("skills", function ($q) use ($name) {
                $q->whereLikeName($name);
            });
        });
    }
    
    public function scopeWhereSponsor($query)
    {
        return $query->whereParticipationType(ProjectParticipantEnum::sponsor->value);
    }
    
    public function scopeWhereIsSponsor($query, $sponsor)
    {
        return $query
            ->whereIsParticipant($sponsor)
            ->whereParticipationType(ProjectParticipantEnum::sponsor->value);
    }
}
