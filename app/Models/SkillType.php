<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SkillType extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'name', 'description'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function skills()
    {
        return $this->hasMany(Skill::class);
    }

    public function hasSkillsAttachedToOtherJobUsers(User $user)
    {
        return $this->skills()->whereNotJobUsersUser($user)->exists();
    }
}
