<?php

namespace App\Actions\Requests;

use App\Actions\Action;
use App\Actions\Project\IsLearnerParticipantTypeAction;
use App\DTOs\RequestDTO;
use App\Enums\ProjectParticipantEnum;
use App\Exceptions\RequestException;
use App\Models\User;

class CanSendRequestForProjectAction extends Action
{
    private bool $isFromOfficial = false;
    private bool $isToOfficial = false;
    private ?string $type = null;
    private ?string $projectParticipatingMethod = null;

    public function execute(RequestDTO $requestDTO)
    {
        $this->setParameters($requestDTO);

        $this->ensureIsEitherFromOfficialOrToOfficial();

        $this->ensureFacilitatorOrLearnerRequestsAreSentByOrToUsers($requestDTO);

        $this->ensureUserHasAppropriateUserType($requestDTO);

        $this->ensureFacilitatorRequestsAreSentByOrToUsersNotAlreadyFacilitatorsOfProject($requestDTO);

        $this->ensureLearnerRequestsAreSentByOrToUsersNotAlreadyLearnersOfProject($requestDTO);

        $this->ensureSponsorRequestsAreSentByOrToUsersNotAlreadySponsorsOfProject($requestDTO);
    }

    private function ensureIsNotFromOfficialAndToOfficial()
    {
        if (!($this->isFromOfficial && $this->isToOfficial)) {
            return;
        }
        
        throw new RequestException("Sorry, you cannot send this request because you are already part of this project.");
    }

    private function ensureIsEitherFromOfficialOrToOfficial()
    {
        $this->ensureIsNotFromOfficialAndToOfficial();

        if ($this->isFromOfficial || $this->isToOfficial) {
            return;
        }
        
        throw new RequestException("Sorry, this request has to be from or to an official of the project.");
    }

    private function setParameters(RequestDTO $requestDTO)
    {
        $this->isFromOfficial = $this->isOfficial($requestDTO, 'from');
        $this->isToOfficial = $this->isOfficial($requestDTO, 'to');
        $this->type = strtolower($requestDTO->type);
        $this->projectParticipatingMethod = 'is' . ucfirst($this->type);
    }

    private function isOfficial(RequestDTO $requestDTO, string $property): bool
    {
        return $requestDTO->for->isOfficial($requestDTO->$property) ||
            (
                $requestDTO->for->isFacilitator($requestDTO->$property) && 
                IsLearnerParticipantTypeAction::make()->execute($requestDTO->type)
            );
    }

    public function ensureFacilitatorOrLearnerRequestsAreSentByOrToUsers(RequestDTO $requestDTO)
    {
        if (!in_array(strtoupper($this->type), [ProjectParticipantEnum::facilitator->value, ProjectParticipantEnum::learner->value])) {
            return;
        }

        $message = null;
        
        if ($this->isFromOfficial && $requestDTO->to::class !== User::class) {
            $message = "Sorry, you can only send this request to a user.";
        }

        if ($this->isToOfficial && $requestDTO->from::class !== User::class) {
            $message = "Sorry, only users can send this request.";
        }

        if (is_null($message)) {
            return;
        }
        
        throw new RequestException($message);
    }

    public function ensureUserHasAppropriateUserType(RequestDTO $requestDTO)
    {
        if (!in_array(strtoupper($this->type), [
            ProjectParticipantEnum::facilitator->value, 
            ProjectParticipantEnum::learner->value,
            ProjectParticipantEnum::sponsor->value,
        ])) {
            return;
        }

        $message = null;
        $projectParticipatingMethod = $this->projectParticipatingMethod;
        
        if (
            $this->isFromOfficial && 
            $this->isUser($requestDTO->to) &&
            !$requestDTO->to->$projectParticipatingMethod()
        ) {
            $message = "Sorry, you cannot send this request because the recepient is not a {$this->getType()}.";
        }

        if (
            $this->isToOfficial && 
            $this->isUser($requestDTO->from) &&
            !$requestDTO->from->$projectParticipatingMethod()
        ) {
            $message = "Sorry, you need to be a {$this->getType()} to request to be a {$this->getType()} in a project";
        }

        if (is_null($message)) {
            return;
        }
        
        throw new RequestException($message);
    }

    public function ensureFacilitatorRequestsAreSentByOrToUsersNotAlreadyFacilitatorsOfProject(RequestDTO $requestDTO)
    {
        $this->ensureRequestsAreSentByOrToUsersNotAlreadyParticipatingAsTypeOfProject(
            $requestDTO, ProjectParticipantEnum::facilitator->value
        );
    }

    public function ensureLearnerRequestsAreSentByOrToUsersNotAlreadyLearnersOfProject(RequestDTO $requestDTO)
    {
        $this->ensureRequestsAreSentByOrToUsersNotAlreadyParticipatingAsTypeOfProject(
            $requestDTO, ProjectParticipantEnum::learner->value
        );
    }

    public function ensureSponsorRequestsAreSentByOrToUsersNotAlreadySponsorsOfProject(RequestDTO $requestDTO)
    {
        $this->ensureRequestsAreSentByOrToUsersNotAlreadyParticipatingAsTypeOfProject(
            $requestDTO, ProjectParticipantEnum::sponsor->value
        );
    }
    
    public function ensureRequestsAreSentByOrToUsersNotAlreadyParticipatingAsTypeOfProject(RequestDTO $requestDTO, string $type)
    {
        $type = strtolower($type);
        
        if ($this->type !== $type) {
            return;
        }

        $message = null;
        $projectParticipatingMethod = $this->projectParticipatingMethod;
        
        if ($this->isFromOfficial && $requestDTO->for->$projectParticipatingMethod($requestDTO->to)) {
            $message = "Sorry, you cannot send this request because the recepient is already a {$this->getType()}.";
        }

        if ($this->isToOfficial && $requestDTO->for->$projectParticipatingMethod($requestDTO->from)) {
            $message = "Sorry, you cannot send this request because you are already a {$this->getType()}.";
        }

        if (is_null($message)) {
            return;
        }
        
        throw new RequestException($message);
    }

    private function getType()
    {
        $type = $this->type == 'learner' ? 'student' : $this->type;

        $value = ProjectParticipantEnum::tryFrom(strtoupper($type))?->name;

        return $value ? strtolower($value) : null;
    }

    private function isUser($model)
    {
        return $model::class === User::class;
    }
}