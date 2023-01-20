<?php

namespace App\Traits;

use App\Enums\RequestStateEnum;
use App\Models\Request;

trait HasRequestForTrait
{
    public function requestedFor()
    {
        return $this->morphMany(Request::class, 'for');
    }

    public function hasPendingRequest(): bool
    {
        return $this->hasRequestState(RequestStateEnum::pending->value);
    }

    public function hasRequestState(string $state): bool
    {
        return $this
            ->whereState($state)
            ->exists();
    }

    public function hasRequestPurpose(string $state): bool
    {
        return $this
            ->whereRequestState($state)
            ->exists();
    }

    public function hasRequestPurposeAndState( string $purpose, string $state): bool
    {
        return $this
            ->whereRequestPurpose($purpose)
            ->whereRequestState($state)
            ->exists();
    }

    public function scopeWhereRequestState($query, $state)
    {
        return $query
            ->requestedFor()
            ->whereState(strtoupper($state));
    }

    public function scopeWhereRequestPurpose($query, $purpose)
    {
        return $query
            ->requestedFor()
            ->wherePurpose($purpose);
    }
}