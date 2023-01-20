<?php 

namespace App\Traits;

use App\Models\Project;

trait CanAddProjectTrait
{
    public function addedProjects()
    {
        return $this->morphMany(Project::class, 'addedby');
    }
}