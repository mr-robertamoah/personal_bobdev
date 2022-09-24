<?php

namespace App\Traits;

use App\Models\Administrator;

trait HasAdminableTrait
{
    public function administrator()
    {
        return $this->morphOne(Administrator::class, Administrator::MORPHNAME);
    }
    
    public function hasAdministrator() : bool
    {
        return $this->has('administrator');
    }
    
    public function doesntHaveAdministrator()
    {
        return ! $this->hasAdministrator();
    }
}
