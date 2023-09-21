<?php

namespace App\Services;

use App\Actions\Activity\AddActivityAction;
use App\Actions\EnsureUserExistsAction;
use App\Actions\GetModelAction;
use App\Actions\Project\EnsureParticipationIsValidArrayAction;
use App\Actions\GetUserDataForUserId;
use App\Actions\Project\EnsureAddedbyIsAuthorizedAction;
use App\Actions\Project\CheckDataAppropriatenessAction;
use App\Actions\Project\EnsureIsValidParticipationTypeAction;
use App\Actions\Project\EnsureParticipantExistsAction;
use App\Actions\Project\EnsureParticipantIsOfValidParticipantClassAction;
use App\Actions\Project\EnsureProjectExistsAction;
use App\Actions\Project\EnsurePotentialParticipantIsNotAlreadyAParticipantAction;
use App\Actions\Project\EnsurePotentialParticipantExistsAction;
use App\Actions\Project\EnsureUserIsParticipatingAsTypeAction;
use App\Actions\Project\LeaveProjectAction;
use App\Actions\Project\RemoveParticipantAction;
use App\Actions\Requests\CreateRequestAction;
use App\Actions\Requests\EnsureFacilitatorCannotRemoveFacilitatorAction;
use App\Actions\SetStartAndEndDatesAction;
use App\Actions\Skills\CheckIfValidSkillsAction;
use App\DTOs\ActivityDTO;
use App\DTOs\ProjectDTO;
use App\DTOs\RequestDTO;
use App\Models\Project;
use App\Models\User;
use App\Models\UserType;

class ProjectService extends Service
{
    const AUTHORIZEDUSERTYPES = [
        UserType::ADMIN,
        UserType::SUPERADMIN,
        UserType::FACILITATOR,
        UserType::STUDENT,
    ];
    
    public function createProject(ProjectDTO $projectDTO): Project|null
    {
        EnsureUserExistsAction::make()->execute($projectDTO);

        EnsureAddedbyIsAuthorizedAction::make()->execute($projectDTO, 'create');

        CheckDataAppropriatenessAction::make()->execute($projectDTO, 'create');

        $project = $projectDTO->addedby->addedProjects()->create(
            [
                ...$projectDTO->getData(filled: true),
                ...SetStartAndEndDatesAction::make()->execute($projectDTO)
            ]
        );

        if ($project instanceOf Project)
        {
            return $project;
        }

        return null;
    }

    public function updateProject(ProjectDTO $projectDTO)
    {
        EnsureUserExistsAction::make()->execute($projectDTO);
        
        $projectDTO = $this->setProjectOnDTO($projectDTO);

        EnsureProjectExistsAction::make()->execute($projectDTO);

        EnsureAddedbyIsAuthorizedAction::make()->execute($projectDTO, 'update');
        
        CheckDataAppropriatenessAction::make()->execute($projectDTO, 'update');

        $projectDTO->project->update($this->getData($projectDTO));

        return $projectDTO->project->refresh();
    }

    public function updateProjectDates(ProjectDTO $projectDTO)
    {
        EnsureUserExistsAction::make()->execute($projectDTO);
        
        $projectDTO = $this->setProjectOnDTO($projectDTO);

        EnsureProjectExistsAction::make()->execute($projectDTO);

        EnsureAddedbyIsAuthorizedAction::make()->execute($projectDTO, 'update');

        $projectDTO->project->update(
            SetStartAndEndDatesAction::make()->execute($projectDTO)
        );

        return $projectDTO->project->refresh();
    }

    public function deleteProject(ProjectDTO $projectDTO)
    {
        EnsureUserExistsAction::make()->execute($projectDTO);
        
        $projectDTO = $this->setProjectOnDTO($projectDTO);

        EnsureProjectExistsAction::make()->execute($projectDTO);

        EnsureAddedbyIsAuthorizedAction::make()->execute($projectDTO, 'delete');
        
        CheckDataAppropriatenessAction::make()->execute($projectDTO, 'delete');

        return $projectDTO->project->delete();
    }

    public function addSkillsToProject(ProjectDTO $projectDTO, array $ids)
    {
        EnsureUserExistsAction::make()->execute($projectDTO);
        
        $projectDTO = $this->setProjectOnDTO($projectDTO);

        EnsureProjectExistsAction::make()->execute($projectDTO);

        EnsureAddedbyIsAuthorizedAction::make()->execute(
            projectDTO: $projectDTO, what: 'skills', action: 'update');

        CheckIfValidSkillsAction::make()->execute($ids);

        $ids = array_diff($ids, $projectDTO->project->skills()->allRelatedIds()->toArray());

        $projectDTO->project->skills()->attach($ids);

        // TODO broadcast on project

        return $projectDTO->project->refresh();
    }

    public function removeSkillsFromProject(ProjectDTO $projectDTO, array $ids)
    {
        EnsureUserExistsAction::make()->execute($projectDTO);
        
        $projectDTO = $this->setProjectOnDTO($projectDTO);

        EnsureProjectExistsAction::make()->execute($projectDTO);

        EnsureAddedbyIsAuthorizedAction::make()->execute(
            projectDTO: $projectDTO, what: 'skills', action: 'update');

        CheckIfValidSkillsAction::make()->execute($ids);

        $ids = array_intersect($ids, $projectDTO->project->skills()->allRelatedIds()->toArray());

        $projectDTO->project->skills()->detach($ids);

        return $projectDTO->project->refresh();
    }

    // TODO broadcast successfull leaving to owner and facilitators

    public function leaveProject(ProjectDTO $projectDTO)
    {
        $projectDTO = $this->setProjectOnDTO($projectDTO);

        EnsureUserExistsAction::make()->execute($projectDTO);
        
        EnsureProjectExistsAction::make()->execute($projectDTO);

        $projectDTO = $projectDTO->withParticipant($projectDTO->addedby);

        EnsureParticipantIsOfValidParticipantClassAction::make()->execute($projectDTO);

        EnsureIsValidParticipationTypeAction::make()->execute($projectDTO);

        EnsureUserIsParticipatingAsTypeAction::make()->execute($projectDTO);

        LeaveProjectAction::make()->execute($projectDTO);
    }

    public function removeParticipants(ProjectDTO $projectDTO)
    {
        $projectDTO = $this->setProjectOnDTO($projectDTO);

        EnsureUserExistsAction::make()->execute($projectDTO);
        
        EnsureProjectExistsAction::make()->execute($projectDTO);

        $isNotFacilitator = $projectDTO->project->isNotFacilitator($projectDTO->addedby);

        if ($isNotFacilitator)
        {
            EnsureAddedbyIsAuthorizedAction::make()->execute($projectDTO, 'remove');
        }
        
        EnsureParticipationIsValidArrayAction::make()->execute($projectDTO);

        foreach ($projectDTO->participations as $userId => $userData)
        {
            [$participationType, $class] = GetUserDataForUserId::make()->execute($userData);
            
            $participant = GetModelAction::make()->execute($class ?? 'user', $userId);

            $projectDTO = $projectDTO->withParticipant($participant);

            EnsureParticipantExistsAction::make()->execute($projectDTO);

            EnsureParticipantIsOfValidParticipantClassAction::make()->execute($projectDTO);

            EnsureIsValidParticipationTypeAction::make()->execute($projectDTO, $participationType);

            EnsureUserIsParticipatingAsTypeAction::make()->execute($projectDTO, $participationType);
    
            if (!$isNotFacilitator)
            {
                EnsureFacilitatorCannotRemoveFacilitatorAction::make()->execute($projectDTO, $participationType);
            }

            RemoveParticipantAction::make()->execute($projectDTO, $participationType);
        }

        AddActivityAction::make()->execute(
            ActivityDTO::new()->fromArray([
                'performedby' => $projectDTO->addedby,
                'performedon' => $projectDTO->project,
                'action' => 'removeParticipants',
                'data' => [
                    'participations' => $projectDTO->participations
                ]
            ])
        );
    }
    
    // TODO add an addParticipant function that just adds participants and can only be used by system admins
    
    public function sendParticipationRequest(ProjectDTO $projectDTO)
    {
        $projectDTO = $this->setProjectOnDTO($projectDTO);

        EnsureUserExistsAction::make()->execute($projectDTO);
        
        EnsureProjectExistsAction::make()->execute($projectDTO);

        $isNotFacilitator = $projectDTO->project->isNotFacilitator($projectDTO->addedby);

        if ($isNotFacilitator)
        {
            EnsureAddedbyIsAuthorizedAction::make()->execute($projectDTO, 'update');
        }

        EnsureParticipationIsValidArrayAction::make()->execute($projectDTO);
        
        $requests = [];
        foreach ($projectDTO->participations as $userId => $userData)
        {
            [$participationType, $class, $purpose] = GetUserDataForUserId::make()->execute($userData);
            
            $possibleParticipant = GetModelAction::make()->execute($class ?? 'user', $userId);

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
            ...SetStartAndEndDatesAction::make()->execute($projectDTO)
        ];
    }

    private function setProjectOnDTO(ProjectDTO $projectDTO): ProjectDTO
    {
        return $projectDTO->withProject(
            $projectDTO->project ?? Project::find($projectDTO->projectId)
        );
    }
}