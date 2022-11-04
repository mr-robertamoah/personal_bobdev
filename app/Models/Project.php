<?php

namespace App\Models;

use app\enums\ProjectParticipantEnum;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use HasFactory,
    SoftDeletes;

    protected $fillable = ['name', 'description', 'start_date', 'end_date'];

    public function startDate(): Attribute
    {
        return Attribute::make(
            get: fn($attributes)=> $attributes['start_date']
        );
    }

    public function endDate(): Attribute
    {
        return Attribute::make(
            get: fn($attributes)=> $attributes['end_date']
        );
    }

    public function facilitators(): Attribute
    {
        return Attribute::make(
            get: fn($attributes)=> $this->users()
                ->wherePivot('participating_as', ProjectParticipantEnum::facilitator->value)
                ->get()
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

    public function users()
    {
        return $this->belongsToMany(User::class)
            ->withPivot(['participating_as'])
            ->withTimestamps();
    }

    public function requested()
    {
        return $this->morphMany(Request::class, 'for');
    }
}
