<?php

namespace App\Traits;

use App\Models\Project;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait HasProjectAddedByTrait
{
    public function addedProjects(): MorphMany
    {
        return $this->morphMany(Project::class, 'addedby');
    }
}