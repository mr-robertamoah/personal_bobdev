<?php

namespace App\Traits;

use App\Models\Project;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait ProjectAddedByTrait
{
    public function addedProjects(): MorphMany
    {
        return $this->morphMany(Project::class, 'addedby');
    }

    public function scopeWhereProject($query, $name)
    {
        return $query->where('name', $name);
    }
}