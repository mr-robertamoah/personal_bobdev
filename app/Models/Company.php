<?php

namespace App\Models;

use App\Abstracts\Requestable;
use App\Enums\RelationshipTypeEnum;
use App\Traits\HasProfileTrait;
use App\Traits\HasRequestForTrait;
use App\Traits\HasProjectParticipantTrait;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Company extends Requestable
{
    use HasFactory,
    HasProjectParticipantTrait,
    HasProfileTrait,
    HasRequestForTrait,
    SoftDeletes;
    
    const ALIASLENGTH = 8;

    protected $fillable = ['name', 'alias', 'about'];

    public function addedby(): Attribute
    {
        return Attribute::make(
            get: fn($attribute) => $this->owner,
        );
    }

    public function owner()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function addedByRelations()
    {
        return $this->morphMany(Relation::class, 'by');
    }

    public function addedToRelations()
    {
        return $this->morphMany(Relation::class, 'to');
    }

    public function members()
    {
        return $this->addedByRelations()
            ->whereIn('relationship_type', RelationshipTypeEnum::companyRelationships())
            ->with('addedToRelations')
            ->get();
    }

    public function isManager(User $user)
    {
        return $this
            ->addedByRelations()
            ->whereUserIsInARelationshipType(
                $user, RelationshipTypeEnum::companyAdministrator->value
            )
            ->exists();
    }

    public function isNotManager(User $user)
    {
        return !$this->isManager($user);
    }

    public function isOfficial(User $user): bool
    {
        return $this->isOwner($user) || $this->isManager($user);
    }

    public function isNotOfficial(User $user): bool
    {
        return !$this->isOfficial($user);
    }

    public function isSponsor()
    {
        return true;
    }

    public function isOwner(User $user): bool
    {
        return $this->owner->is($user);
    }

    public function isNotOwner(User $user): bool
    {
        return !$this->isOwner($user);
    }

    public function isMember(User $user)
    {
        return $this
            ->addedByRelations()
            ->whereUserIsInARelationshipType(
                $user, RelationshipTypeEnum::companyMember->value
            )
            ->exists();
    }

    public function isNotMember(User $user): bool
    {
        return !$this->isMember($user);
    }

    public function getRelationship(User $user): Relation
    {
        return $this
            ->addedByRelations()
            ->whereTo($user)
            ->first();
    }

    public function getRelationshipAlias(User $user)
    {
        $relationship = $this->getRelationship($user);

        if (is_null($relationship)) {
            return null;
        }

        return strtolower($relationship->relationship_type);
    }
}
