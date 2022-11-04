<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class JobUser extends Model
{
    protected $table = 'job_user';
    
    public $fillable = ['user_id', 'job_id'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function job()
    {
        return $this->belongsTo(Job::class);
    }

    public function jobUserSkills()
    {
        return $this->hasMany(JobUserSkill::class);
    }

    public function hasSkillWithLevel($levelId): bool
    {
        return $this->whereHasSkillWithLevel($levelId)->exists();
    }

    public function doesNotHaveSkillWithLevel($levelId): bool
    {
        return !$this->hasSkillWithLevel($levelId);
    }

    public function scopeWhereUser($query, $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeWhereNotUser($query, $user)
    {
        return $query->where('user_id', '<>', $user->id);
    }

    public function scopeWhereHasSkillWithLevelId($query, $levelId)
    {
        return $query->whereHas('jobUserSkills', function ($query) use ($levelId) {
            $query->where('level_id', $levelId);
        });
    }
}