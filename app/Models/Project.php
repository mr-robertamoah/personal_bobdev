<?php

namespace App\Models;

use app\enums\ProjectParticipantEnum;
use App\Traits\HasRequestForTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Abstracts\Requestable;

class Project extends Requestable
{
    use HasFactory,
    HasRequestForTrait,
    SoftDeletes;

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
                return $this->users()
                    ->wherePivot('participating_as', ProjectParticipantEnum::facilitator->value)
                    ->get();
            }
        );
    }

    public function learners(): Attribute
    {
        return Attribute::make(
            get: fn($attributes)=> $this->users()
                ->wherePivot('participating_as', ProjectParticipantEnum::learner->value)
                ->get()
        );
    }

    public function sponsors(): Attribute
    {
        return Attribute::make(
            get: fn($attributes)=> $this->users()
                ->wherePivot('participating_as', ProjectParticipantEnum::sponsor->value)
                ->get()
        );
    }

    public function addedby()
    {
        return $this->morphTo();
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
            ->whereParticipant($model)
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
            ->whereParticipant($model)
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
            ->where('addedby_type', $model::class)
            ->where('addedby_id', $model->id)
            ->exists();
    }

    public function isOwnedByCompany()
    {
        return $this->addedby::class === Company::class;
    }

    public function getProjectParticipant(Model $model, string $type): ?ProjectParticipant
    {
        return $this->participants()
            ->whereParticipant($model)
            ->whereParticipationType($type)
            ->first();
    }
}
