<?php

namespace App\Actions\Requests;

use App\Actions\Action;
use App\DTOs\RequestDTO;
use App\Enums\RelationshipTypeEnum;
use App\Exceptions\RequestException;
use App\Models\User;

class CanSendRequestForUserAction extends Action
{
    private bool $isFromParent = false;

    public function execute(RequestDTO $requestDTO)
    {
        $this->setParameters($requestDTO);

        $this->ensureRequestIsFromUserToUser($requestDTO);

        $this->ensureToAndFromAreDifferentUsers($requestDTO);

        $this->ensureParentIsAnAdult($requestDTO);
    }

    private function ensureRequestIsFromUserToUser(RequestDTO $requestDTO)
    {
        if (
            $requestDTO->from::class == User::class &&
            $requestDTO->to::class == User::class
        ) return;
        
        throw new RequestException("Sorry, a parent-ward relationship should be between two users.");
    }

    private function ensureToAndFromAreDifferentUsers(RequestDTO $requestDTO)
    {
        if (
            !$requestDTO->to->is($requestDTO->from)
        ) return;
        
        throw new RequestException("Sorry, requests cannot be sent from and to the same user.");
    }

    private function ensureParentIsAnAdult(RequestDTO $requestDTO)
    {
        if (
            ($this->isFromParent && $requestDTO->from->isAdult()) ||
            (!$this->isFromParent && $requestDTO->to->isAdult())
        ) return;
        
        throw new RequestException("Sorry, you cannot send a request from/to a parent who is not an adult.");
    }

    private function setParameters(RequestDTO $requestDTO)
    {
        $this->isFromParent = strtolower(RelationshipTypeEnum::parent->value) == 
            strtolower($requestDTO->type);
    }
}