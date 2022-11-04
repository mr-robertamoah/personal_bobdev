<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JobUserSkill extends Model
{
    use HasFactory;

    protected $table = 'job_user_skill';

    protected $fillable = ['job_user_id', 'skill_id', 'level_id'];

    public function jobUser()
    {
        return $this->belongsTo(JobUser::class);
    }

    public function skill()
    {
        return $this->belongsTo(skill::class);
    }

    public function scopeWhereLevel($query, $levelId)
    {
        return $query->where('level_id', $levelId);
    }

    public function scopeWhereUser($query, $user)
    {
        return $query->whereHas('jobUser', function($query) use ($user) {
            $query->whereUser($user);
        });
    }

    public function scopeWhereNotUser($query, $user)
    {
        return $query->whereHas('jobUser', function($query) use ($user) {
            $query->whereNotUser($user);
        });
    }
}
