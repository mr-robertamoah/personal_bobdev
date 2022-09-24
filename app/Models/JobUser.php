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

    public function skills()
    {
        return $this->belongsToMany(Skill::class, 'job_user_skill')
            ->withTimestamps();
    }

    public function scopeWhereUser($query, $user)
    {
        return $query->where('user_id', $user->id);
    }

    public function scopeWhereNotUser($query, $user)
    {
        return $query->where('user_id', '<>', $user->id);
    }
}