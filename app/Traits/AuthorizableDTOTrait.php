<?php

namespace App\Traits;

use App\Models\Company;
use App\Models\User;

trait AuthorizableDTOTrait
{
    public ?User $official = null;
    public ?User $member = null;
    public ?string $name = null;
    public ?string $like = null;
    public string|int|null $ownerId = null;
    public string|null $ownerType = null;
    public string|int|null $memberId = null;
    public string|int|null $officialId = null;
    public string|int|null $participantId = null;
    public string|null $participantType = null;
    public int|string|null $page = null;
    public User|Company|null $participant = null;

    public function isForNextPage() : bool
    {
        return !is_null($this->page);
    }
}