<?php

namespace App\Models;

use App\Exceptions\UserTypeException;
use App\Traits\CanAddImagesTrait;
use App\Traits\HasAdministratorTrait;
use App\Traits\HasProfileTrait;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, 
        Notifiable,
        HasProfileTrait,
        HasAdministratorTrait,
        CanAddImagesTrait;
    
    const MALE = 'MALE';
    const FEMALE = 'FEMALE';

    protected $fillable = [
        'first_name',
        'surname',
        'other_names',
        'username',
        'gender',
        'email',
        'password',
    ];
    
    protected $hidden = [
        'password',
        'remember_token',
    ];
    
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // start of attributes
    public function name() : Attribute
    {
        return new Attribute(
            get: function ($value, $attributes) {
                $name = "{$attributes['surname']} {$attributes['first_name']}";

                if ($attributes['other_names']) {
                    $name = $name . " {$attributes['other_names']}";
                }
                
                return $name;
            }
        );
    }
    // end of attributes

    // start of relationships
    public function addedUserTypes() 
    {
        return $this->hasMany(
            related: UserType::class, 
            foreignKey: 'user_id'
        );
    }

    public function userTypes()
    {
        return $this->belongsToMany(
            related: UserType::class,
            table: 'user_user_type'
        )->withTimestamps();
    }

    public function skillTypes()
    {
        return $this->hasMany(SkillType::class);
    }

    public function addedSkills()
    {
        return $this->hasMany(Skill::class, 'user_id');
    }

    public function addedLevels()
    {
        return $this->hasMany(Level::class, 'user_id');
    }

    public function addedLevelCollections()
    {
        return $this->hasMany(LevelCollection::class, 'user_id');
    }

    public function addedJobs()
    {
        return $this->hasMany(Job::class, 'user_id');
    }

    public function jobUsers()
    {
        return $this->hasMany(JobUser::class);
    }

    public function jobUserFromJobID($jobId)
    {
        return $this->jobUsers()->where('job_id', $jobId)->first();
    }

    public function activities()
    {
        return $this->morphMany(Activity::class, 'performedby');
    }
    // end of relationships

    // start of methods
    public function isAdmin() : bool
    {
        return $this->userTypes()
            ->whereIn('name', [UserType::ADMIN, UserType::SUPERADMIN])
            ->exists();
    }

    public function isFacilitator() : bool
    {
        return $this->userTypes()
            ->where('name', UserType::FACILITATOR)
            ->exists();
    }

    public function isSuperAdmin() : bool
    {
        return $this->userTypes()
            ->where('name',UserType::SUPERADMIN)
            ->exists();
    }

    public function normalUserTypes()
    {
        return $this->userTypes()
            ->where('name', '<>', UserType::SUPERADMIN)
            ->get();
    }

    public function getAllUserTypes() : array
    {
        return $this->userTypes()
            ->pluck('name')
            ->map(fn($name)=> UserType::USABLETYPES[$name])
            ->toArray();
    }

    public function hasUserType($name)
    {
        return $this->userTypes()
            ->where('name', strtoupper($name))
            ->exists();
    }
    // end of methods

    // start of scopes
    public function scopeWhereUserType($query, $name)
    {
        return $query->whereHas('userTypes', function($query) use ($name) {
            $query->where('name', $name);
        });
    }
    
    public function scopeWhereUserTypes($query, $names)
    {
        return $query->whereHas('userTypes', function($query) use ($names) {
            $query->whereIn('name', $names);
        });
    }
    // end of scopes
}
