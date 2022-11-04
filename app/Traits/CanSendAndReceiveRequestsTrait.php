<?php

namespace App\Traits;

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
}