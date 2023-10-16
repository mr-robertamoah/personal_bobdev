<?php 

namespace App\Traits;

use App\Enums\ProjectParticipantEnum;
use App\Models\Project;
use App\Models\ProjectParticipant;

trait HasProjectParticipantTrait
{
    public function participations()
    {
        return $this->morphMany(ProjectParticipant::class, 'participant');
    }

    public function isParticipatingInProject(Project $project): bool
    {
        return $this->participations()
            ->whereParticipatingInProject($project)
            ->exists();
    }

    public function scopeWhereParticipatingInProject($query, Project $project)
    {
        return $query->whereHas("participations", function ($q) use ($project) {
            $q->where('project_id', $project->id);
        });
    }

    public function sponsoredProjectsQuery()
    {
        return Project::query()
            ->whereSponsor()
            ->whereIsParticipant($this);
    }

    public function sponsoredProjects()
    {
        return $this->sponsoredProjectsQuery()->get();
    }

    public function scopeWhereParticipatingAsSponsor($query)
    {
        return $query
            ->whereParticipatingAs(ProjectParticipantEnum::sponsor->value);
    }

    public function scopeWhereParticipatingAs($query, string $type)
    {
        return $query->whereHas("participations", function ($q) use ($type) {
            $q->where('participating_as', $type);
        });
    }
}