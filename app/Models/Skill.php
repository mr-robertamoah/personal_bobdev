<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Skill extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'description', 'skill_type_id'
    ];

    public function addedBy()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function skillType()
    {
        return $this->belongsTo(SkillType::class);
    }

    public function users()
    {
        return $this->belongsToMany(User::class);
    }

    public function levels()
    {
        return $this->belongsToMany(Level::class);
    }

    public function jobUsers()
    {
        return $this->belongsToMany(JobUser::class, 'job_user_skill')
            ->withTimestamps();
    }

    public function scopeWhereAddedBy($query, $user)
    {
        return $query->whereHas('addedBy', function($query) use($user) {
            $query->where('user_id', $user->id);
        });
    }

    public function scopeWhereNotAddedBy($query, $user)
    {
        return $query->whereHas('addedBy', function($query) use($user) {
            $query->where('user_id', '<>', $user->id);
        });
    }

    public function scopeWhereUser($query, $user)
    {
        return $query->whereHas('users', function($query) use($user) {
            $query->where('user_id', $user->id);
        });
    }

    public function scopeWhereNotUser($query, $user)
    {
        return $query->whereHas('users', function($query) use($user) {
            $query->where('user_id', '<>', $user->id);
        });
    }

    public function scopeWhereJobUsersUser($query, $user)
    {
        return $query->whereHas('jobUsers', function($query) use($user) {
            $query->whereUser($user);
        });
    }

    public function scopeWhereNotJobUsersUser($query, $user)
    {
        return $query->whereHas('jobUsers', function($query) use($user) {
            $query->whereNotUser($user);
        });
    }
}
