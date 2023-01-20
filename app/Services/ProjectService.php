<?php

namespace App\Services;

use App\Actions\Project\BecomeFacilitatorOfProjectAction;
use App\Actions\Project\BecomeLearnerOfProjectAction;
use App\Actions\Project\BecomeProjectParticipantAction;
use App\Actions\Project\BecomeSponsorOfProjectAction;
use App\Actions\Project\EnsureAddedByExistsAction;
use App\Actions\Project\EnsureIsAuthorizedAction;
use App\Actions\Project\CheckDataAppropriatenessAction;
use App\Actions\Project\EnsureIsValidParticipantTypeAction;
use App\Actions\Project\EnsureProjectExistsAction;
use App\Actions\Project\EnsureParticipantExistsAction;
use App\Actions\Project\EnsureParticipantNotAlreadyAParticipantAction;
use App\Actions\Skills\CheckIfValidSkillsAction;
use App\DTOs\ProjectDTO;
use App\Enums\ProjectParticipantEnum;
use App\Models\Project;
use App\Models\User;
use App\Models\UserType;
use Carbon\Carbon;
use DateTime;

class ProjectService
{
    const AUTHORIZEDUSERTYPES = [
        UserType::ADMIN,
        UserType::SUPERADMIN,
        UserType::FACILITATOR,
        UserType::STUDENT,
    ];
    
    public function createProject(ProjectDTO $projectDTO): Project
    {
        EnsureAddedByExistsAction::make()->execute($projectDTO);

        EnsureIsAuthorizedAction::make()->execute($projectDTO, 'create');

        CheckDataAppropriatenessAction::make()->execute($projectDTO, 'create');

        return $projectDTO->addedby->addedProjects()->create(
            [
                ...$projectDTO->getData(filled: true),
                ...$this->setDates($projectDTO)
            ]
        );
    }

    public function updateProject(ProjectDTO $projectDTO)
    {
        EnsureAddedByExistsAction::make()->execute($projectDTO);
        
        $projectDTO = $this->setProjectOnDTO($projectDTO);

        EnsureProjectExistsAction::make()->execute($projectDTO);

        EnsureIsAuthorizedAction::make()->execute($projectDTO, 'update');
        
        CheckDataAppropriatenessAction::make()->execute($projectDTO, 'update');

        $projectDTO->project->update($this->getData($projectDTO));

        return $projectDTO->project->refresh();
    }

    public function updateProjectDates(ProjectDTO $projectDTO)
    {
        EnsureAddedByExistsAction::make()->execute($projectDTO);
        
        $projectDTO = $this->setProjectOnDTO($projectDTO);

        EnsureProjectExistsAction::make()->execute($projectDTO);

        EnsureIsAuthorizedAction::make()->execute($projectDTO, 'update');

        $projectDTO->project->update($this->setDates($projectDTO));

        return $projectDTO->project->refresh();
    }

    public function deleteProject(ProjectDTO $projectDTO)
    {
        EnsureAddedByExistsAction::make()->execute($projectDTO);
        
        $projectDTO = $this->setProjectOnDTO($projectDTO);

        EnsureProjectExistsAction::make()->execute($projectDTO);

        EnsureIsAuthorizedAction::make()->execute($projectDTO, 'delete');
        
        CheckDataAppropriatenessAction::make()->execute($projectDTO, 'delete');

        return $projectDTO->project->delete();
    }

    public function addSkillsToProject(ProjectDTO $projectDTO, array $ids)
    {
        EnsureAddedByExistsAction::make()->execute($projectDTO);
        
        $projectDTO = $this->setProjectOnDTO($projectDTO);

        EnsureProjectExistsAction::make()->execute($projectDTO);

        EnsureIsAuthorizedAction::make()->execute($projectDTO, 'update');

        CheckIfValidSkillsAction::make()->execute($ids);

        $projectDTO->project->skills()->sync($ids);

        return $projectDTO->project->refresh();
    }

    public function addParticipantToProject(ProjectDTO $projectDTO)
    {
        EnsureAddedByExistsAction::make()->execute($projectDTO);
        
        $projectDTO = $this->setProjectOnDTO($projectDTO);

        EnsureProjectExistsAction::make()->execute($projectDTO);

        EnsureIsAuthorizedAction::make()->execute($projectDTO, 'update');
        
        $projectDTO = $this->setParticipantOnDTO($projectDTO);
        
        EnsureParticipantExistsAction::make()->execute($projectDTO);

        EnsureParticipantNotAlreadyAParticipantAction::make()->execute($projectDTO);

        EnsureIsValidParticipantTypeAction::make()->execute($projectDTO);

        BecomeProjectParticipantAction::make()->execute($projectDTO);
    }

    private function getData(ProjectDTO $projectDTO) : array
    {
        $data = [];

        if ($projectDTO->name) {
            $data['name'] = $projectDTO->name;
        }

        if ($projectDTO->description) {
            $data['description'] = $projectDTO->description;
        }

        return [
            ...$data,
            ...$this->setDates($projectDTO)
        ];
    }

    private function setProjectOnDTO(ProjectDTO $projectDTO): ProjectDTO
    {
        return $projectDTO->withProject(
            $projectDTO->project ?? Project::find($projectDTO->projectId)
        );
    }

    private function setParticipantOnDTO(ProjectDTO $projectDTO): ProjectDTO
    {
        return $projectDTO->withParticipant(
            $projectDTO->participant ?? User::find($projectDTO->participantId)
        );
    }

    private function setDates(ProjectDTO $projectDTO)
    {
        $dates = [];

        if ($projectDTO->startDate) {
            $dates['start_date'] = $this->transformDate($projectDTO->startDate);
        }

        if ($projectDTO->endDate) {
            $dates['end_date'] = $this->transformDate($projectDTO->endDate);
        }

        return $dates;
    }

    private function transformDate(string|DateTime|Carbon|null $date): string|null
    {
        if (is_null($date)) {
            return $date;
        }
        
        if (is_string($date) && Carbon::parse($date)->isValid()) {
            return Carbon::parse($date)->toDateTimeString();
        }
        
        if ($date instanceof Carbon) {
            return $date->toDateTimeString();
        }
        
        if ($date instanceof DateTime) {
            return $date->format('Y-m-d H:i:s Z');
        }

        return null;
    }
}