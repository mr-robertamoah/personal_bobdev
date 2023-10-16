<?php

namespace App\Models;

use App\Abstracts\Requestable;
use App\Enums\RelationshipTypeEnum;
use App\Traits\HasAuthorizableTrait;
use App\Traits\HasProfileTrait;
use App\Traits\HasProjectAddedByTrait;
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
    HasAuthorizableTrait,
    SoftDeletes,
    HasProjectAddedByTrait;
    
    const ALIASLENGTH = 8;
    const PROJECTTYPES = [
        "all", "added", "sponsored"
    ];

    protected $fillable = ['name', 'alias', 'about', 'user_id'];

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

    public function participations()
    {
        return $this->morphMany(ProjectParticipant::class, 'participant');
    }

    public function allMembersQuery()
    {
        return Relation::query()
            ->whereIsRelated($this)
            ->with('to')
            ->with('by');
    }

    public function allMembers()
    {
        return $this->allMembersQuery()->get();
    }

    public function membersQuery()
    {
        return Relation::query()
            ->whereIsRelated($this)
            ->whereIsRelationshipType(RelationshipTypeEnum::companyMember->value)
            ->with('to')
            ->with('by');
    }

    public function members()
    {
        return $this->membersQuery()->get();
    }

    public function officialsQuery()
    {
        return Relation::query()
            ->whereIsRelated($this)
            ->whereIsRelationshipType(RelationshipTypeEnum::companyAdministrator->value)
            ->with('to')
            ->with('by');
    }

    public function officials()
    {
        return $this->officialsQuery()->get();
    }

    public function isManager(User $user)
    {
        return $this
            ->whereIsOfficial($user)
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
            ->whereIsMember($user)
            ->exists();
    }

    public function isNotMember(User $user): bool
    {
        return !$this->isMember($user);
    }

    public function isNotParticipant(User $user): bool
    {
        return !$this->isMember($user);
    }

    public function getRelationship(User $user): Relation
    {
        return $this
            ->addedByRelations()
            ->whereIsTo($user)
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

    public function scopeWhereIsOfficial($query, User $user)
    {
        return $query
            ->whereUserIsOfRelationshipType($user, RelationshipTypeEnum::companyAdministrator->value);
    }

    public function scopeWhereIsMember($query, User $user)
    {
        return $query
            ->whereUserIsOfRelationshipType($user, RelationshipTypeEnum::companyMember->value);
    }

    public function scopeWhereUserIsOfRelationshipType($query, User $user, ?string $type = null)
    {
        return $query
            ->whereHas("addedByRelations", function ($q) use ($user, $type) {
                $q->whereIsTo($user);
                if (!is_null($type)) $q->whereIsRelationshipType($type);
            })->orWhereHas("addedToRelations", function ($q) use ($user, $type) {
                $q->whereIsBy($user);
                if (!is_null($type)) $q->whereIsRelationshipType($type);
            });
    }

    public function scopeWhereIsOfRelationshipType($query, ?string $type = null)
    {
        return $query
            ->whereHas("addedByRelations", function ($q) use ($type) {
                $q->whereIsRelationshipType($type);
            })->orWhereHas("addedToRelations", function ($q) use ($type) {
                $q->whereIsRelationshipType($type);
            });
    }
}
