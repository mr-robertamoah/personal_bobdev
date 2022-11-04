<?php

namespace App\Models;

use App\Enums\CompanyMemberEnum;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    use HasFactory;

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function members()
    {
        return $this->belongsToMany(User::class, 'company_user')
            ->withPivot('type')
            ->withTimestamps();
    }

    public function isManager(User $user)
    {
        return $this->members()
            ->where('user_id', $user->id)
            ->wherePivot('type', CompanyMemberEnum::manager->value)
            ->exist();
    }

    public function isMember(User $user)
    {
        return $this->members()
            ->where('user_id', $user->id)
            ->wherePivot('type', CompanyMemberEnum::member->value)
            ->exist();
    }
}
