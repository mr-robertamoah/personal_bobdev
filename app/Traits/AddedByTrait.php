<?php

namespace App\Traits;

trait AddedByTrait
{
    public function addedby($name = null)
    {
        return $this->morphTo(name: $name);
    }
}