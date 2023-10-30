<?php

namespace App\Models;

use App\Enums\ProjectParticipantEnum;
use App\Traits\CanAddImagesTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Profile extends Model
{
    use HasFactory,
        CanAddImagesTrait;

    const PROFILEABLECLASSES = [
        User::class,
        Company::class,
    ];

    protected $fillable = [
        'about',
        'settings'
    ];

    protected $casts = [
        'settings' => 'array'
    ];

    public function user() : Attribute
    {
        return new Attribute(
            get: fn() => $this->profileable::class == User::class ?
                $this->profileable : null
        );
    }

    public function company() : Attribute
    {
        return new Attribute(
            get: fn() => $this->profileable::class == Company::class ?
                $this->profileable : null
        );
    }

    public function profileable()
    {
        return $this->morphTo();
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function emails()
    {
        return $this->contacts()
            ->where('type', Contact::EMAIL)
            ->get();
    }

    public function phones()
    {
        return $this->contacts()
            ->where('type', Contact::PHONE)
            ->get();
    }

    // start of methods
    public function isForUser(): bool
    {
        return $this->profileable::class == User::class;
    }

    public function isForCompany(): bool
    {
        return $this->profileable::class == Company::class;
    }

    public function isAdmin() : bool
    {
        return $this->isForUser() && $this->profileable->isAdmin();
    }

    public function isParent() : bool
    {
        return $this->isForUser() && $this->profileable->isParent();
    }

    public function isFacilitator() : bool
    {
        return $this->isForUser() && $this->profileable->isFacilitator();
    }

    public function isLearner()
    {
        return $this->isStudent();
    }

    public function isSponsor()
    {
        return $this->isDonor();
    }

    public function isStudent() : bool
    {
        return $this->isForUser() && $this->profileable->isLearner();
    }

    public function isDonor() : bool
    {
        return $this->isForUser() && $this->profileable->isDonor();
    }

    public function isSuperAdmin() : bool
    {
        return $this->isForUser() && $this->profileable->isSuperAdmin();
    }

    public function normalUserTypes()
    {
        return $this->isForUser() && $this->profileable->userTypes()
            ->where('name', '<>', UserType::SUPERADMIN)
            ->exists();
    }

    public function ownedProjects()
    {
        return $this->profileable->addedProjects;
    }

    public function loadFacilitatorProjects(): void
    {
        if ($this::class != User::class) {
            return;
        }

        $this->loadMorph('profileable', [
            User::class => function($query)
            {
                $query->load('projects', function ($query){
                    $query->where('participating_as', ProjectParticipantEnum::facilitator->value);
                });
            }
        ]);
    }

    public function loadLearnerProjects(): void
    {
        if ($this::class != User::class) {
            return;
        }
        
        $this->loadMorph('profileable', [
            User::class => function($query)
            {
                $query->load('projects', function ($query){
                    $query->where('participating_as', ProjectParticipantEnum::learner->value);
                });
            }
        ]);
    }

    public function loadParentProjects(): void
    {
        if ($this::class != User::class) {
            return;
        }
        
        $this->loadMorph('profileable', [
            User::class => function($query)
            {
                $query->load('relations', function ($query){
                    $query->load('projects', function($query) {
                        $query->where('participating_as', ProjectParticipantEnum::learner->value);
                    });
                });
            }
        ]);
    }
}
