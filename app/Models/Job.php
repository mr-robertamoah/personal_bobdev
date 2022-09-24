<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Job extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'description',
    ];

    public function addedby()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function jobUsers()
    {
        return $this->hasMany(JobUser::class);
    }

    public function jobUserFromUserID($userId)
    {
        return $this->jobUsers()->where('user_id', $userId)->first();
    }

    public function activities()
    {
        return $this->morphMany(Activity::class, 'performedon');
    }
}
