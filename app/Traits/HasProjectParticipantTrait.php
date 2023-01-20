<?php 

namespace App\Traits;

use App\Models\Project;
use App\Models\ProjectParticipant;

trait HasProjectParticipantTrait
{
    public function projects()
    {
        return $this->morphMany(ProjectParticipant::class, 'participant');
    }

    public function isParticipant(Project $project): bool
    {
        return $this->projects()->where('project_id', $project->id);
    }
}