<?php

namespace App\Traits;

use App\Models\Administrator;

trait HasAdministratorTrait
{
    public function administrator()
    {
        return $this->hasOne(Administrator::class);
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
