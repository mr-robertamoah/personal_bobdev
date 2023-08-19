<?php

namespace App\Services;

use App\Actions\Activity\AddActivityAction;
use App\Actions\Project\EnsureParticipationIsValidArrayAction;
use App\Actions\GetUserDataForUserId;
use App\Actions\Project\BecomeProjectParticipantAction;
use App\Actions\Project\EnsureAddedByExistsAction;
use App\Actions\Project\EnsureAddedbyIsAuthorizedAction;
use App\Actions\Project\CheckDataAppropriatenessAction;
use App\Actions\Project\EnsureIsValidParticipationTypeAction;
use App\Actions\Project\EnsureProjectExistsAction;
use App\Actions\Project\EnsurePotentialParticipantIsNotAlreadyAParticipantAction;
use App\Actions\Project\EnsurePotentialParticipantExistsAction;
use App\Actions\Project\EnsureProjectHasSkillsAction;
use App\Actions\Project\EnsureUserIsParticipatingAsTypeAction;
use App\Actions\Requests\CreateRequestAction;
use App\Actions\Skills\CheckIfValidSkillsAction;
use App\Actions\Users\FindUserByIdAction;
use App\DTOs\ActivityDTO;
use App\DTOs\ProjectDTO;
use App\DTOs\RequestDTO;
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

        EnsureAddedbyIsAuthorizedAction::make()->execute($projectDTO, 'create');

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

        EnsureAddedbyIsAuthorizedAction::make()->execute($projectDTO, 'update');
        
        CheckDataAppropriatenessAction::make()->execute($projectDTO, 'update');

        $projectDTO->project->update($this->getData($projectDTO));

        return $projectDTO->project->refresh();
    }

    public function updateProjectDates(ProjectDTO $projectDTO)
    {
        EnsureAddedByExistsAction::make()->execute($projectDTO);
        
        $projectDTO = $this->setProjectOnDTO($projectDTO);

        EnsureProjectExistsAction::make()->execute($projectDTO);

        EnsureAddedbyIsAuthorizedAction::make()->execute($projectDTO, 'update');

        $projectDTO->project->update($this->setDates($projectDTO));

        return $projectDTO->project->refresh();
    }

    public function deleteProject(ProjectDTO $projectDTO)
    {
        EnsureAddedByExistsAction::make()->execute($projectDTO);
        
        $projectDTO = $this->setProjectOnDTO($projectDTO);

        EnsureProjectExistsAction::make()->execute($projectDTO);

        EnsureAddedbyIsAuthorizedAction::make()->execute($projectDTO, 'delete');
        
        CheckDataAppropriatenessAction::make()->execute($projectDTO, 'delete');

        return $projectDTO->project->delete();
    }

    public function addSkillsToProject(ProjectDTO $projectDTO, array $ids)
    {
        EnsureAddedByExistsAction::make()->execute($projectDTO);
        
        $projectDTO = $this->setProjectOnDTO($projectDTO);

        EnsureProjectExistsAction::make()->execute($projectDTO);

        EnsureAddedbyIsAuthorizedAction::make()->execute(
            projectDTO: $projectDTO, what: 'skill');

        CheckIfValidSkillsAction::make()->execute($ids);

        $ids = array_diff($ids, $projectDTO->project->skills()->allRelatedIds()->toArray());

        $projectDTO->project->skills()->attach($ids);

        return $projectDTO->project->refresh();
    }

    public function removeSkillsFromProject(ProjectDTO $projectDTO, array $ids)
    {
        EnsureAddedByExistsAction::make()->execute($projectDTO);
        
        $projectDTO = $this->setProjectOnDTO($projectDTO);

        EnsureProjectExistsAction::make()->execute($projectDTO);

        EnsureAddedbyIsAuthorizedAction::make()->execute(
            projectDTO: $projectDTO, what: 'skill');

        CheckIfValidSkillsAction::make()->execute($ids);

        $ids = array_intersect($ids, $projectDTO->project->skills()->allRelatedIds()->toArray());

        $projectDTO->project->skills()->detach($ids);

        return $projectDTO->project->refresh();
    }

    // TODO remove participant or participant leaving

    public function removeParticipants(ProjectDTO $projectDTO)
    {
        $projectDTO = $this->setProjectOnDTO($projectDTO);

        EnsureAddedByExistsAction::make()->execute($projectDTO);
        
        EnsureProjectExistsAction::make()->execute($projectDTO);

        EnsureAddedbyIsAuthorizedAction::make()->execute($projectDTO, 'remove');
        
        foreach ($projectDTO->participations as $userId => $userData)
        {
            [$participationType, $purpose] = GetUserDataForUserId::make()->execute($userData);
            
            $participant = FindUserByIdAction::make()->execute($userId);

            $projectDTO = $projectDTO->withParticipant($participant);
            
            EnsurePotentialParticipantExistsAction::make()->execute($projectDTO);

            EnsureUserIsParticipatingAsTypeAction::make()->execute($projectDTO, $participationType);

            EnsureIsValidParticipationTypeAction::make()->execute($projectDTO, $participationType);

            EnsureCannotRemoveFacilitatorIfFacilitatorAction::make()->execute($projectDTO, $participationType);

            $requests[] = CreateRequestAction::make()->execute(
                RequestDTO::new()->fromArray([
                    'from' => $projectDTO->addedby,
                    'for' => $projectDTO->project,
                    'to' => $possibleParticipant,
                    'type' => strtoupper(
                        $participationType ? $participationType : $projectDTO->participationType),
                    'purpose' => $purpose
                ])
            );
        }

        AddActivityAction::make()->execute(
            ActivityDTO::new()->fromArray([
                'performedby' => $projectDTO->addedby,
                'performedon' => $projectDTO->project,
                'action' => 'sendParticipationRequests',
                'data' => [
                    'requests' => array_map(fn($request) => $request->id, $requests)
                ]
            ])
        );

        return $requests;
    }
    
    // TODO add an addParticipant function that just adds participants and can only be used by system admins
    
    public function sendParticipationRequest(ProjectDTO $projectDTO)
    {
        $projectDTO = $this->setProjectOnDTO($projectDTO);

        EnsureAddedByExistsAction::make()->execute($projectDTO);
        
        EnsureProjectExistsAction::make()->execute($projectDTO);

        if ($isNotFacilitator = $projectDTO->project->isNotFacilitator($projectDTO->addedby))
        {
            EnsureAddedbyIsAuthorizedAction::make()->execute($projectDTO, 'update');
        }

        EnsureParticipationIsValidArrayAction::make()->execute($projectDTO);
        
        $requests = [];
        foreach ($projectDTO->participations as $userId => $userData)
        {
            [$participationType, $purpose] = GetUserDataForUserId::make()->execute($userData);
            
            $possibleParticipant = FindUserByIdAction::make()->execute($userId);

            $projectDTO = $projectDTO->withParticipant($possibleParticipant);
            
            EnsurePotentialParticipantExistsAction::make()->execute($projectDTO);

            EnsurePotentialParticipantIsNotAlreadyAParticipantAction::make()->execute($projectDTO);

            EnsureIsValidParticipationTypeAction::make()->execute($projectDTO, $participationType);

            if (! $isNotFacilitator)
            {
                EnsureAddedbyIsAuthorizedAction::make()->execute($projectDTO, 'update', $participationType);
            }

            $requests[] = CreateRequestAction::make()->execute(
                RequestDTO::new()->fromArray([
                    'from' => $projectDTO->addedby,
                    'for' => $projectDTO->project,
                    'to' => $possibleParticipant,
                    'type' => strtoupper(
                        $participationType ? $participationType : $projectDTO->participationType),
                    'purpose' => $purpose
                ])
            );
        }

        AddActivityAction::make()->execute(
            ActivityDTO::new()->fromArray([
                'performedby' => $projectDTO->addedby,
                'performedon' => $projectDTO->project,
                'action' => 'sendParticipationRequests',
                'data' => [
                    'requests' => array_map(fn($request) => $request->id, $requests)
                ]
            ])
        );

        return $requests;
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
            User::find($projectDTO->participantId)
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