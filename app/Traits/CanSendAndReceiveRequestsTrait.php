<?php

namespace App\Traits;

use App\Models\Request;

trait CanSendAndReceiveRequestsTrait
{
    public function sentRequests()
    {
        return $this->morphMany(Request::class, 'from');
    }

    public function receivedRequests()
    {
        return $this->morphMany(Request::class, 'to');
    }

    public function hasPendingRequests()
    {
        return $this->whereHasPendingRequests()->exists();
    }

    public function scopeWhereHasPendingRequests($query)
    {
        return $query->whereHas("receivedRequests", function ($q) {
            $q->wherePending();
        });
    }
}