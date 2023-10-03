<?php

namespace App\Actions;
use App\Actions\Action;
use App\DTOs\AuthorizationDTO;
use App\Enums\PermissionEnum;
use App\Exceptions\AuthorizationException;
use App\Models\Authorization;

class EnsureUserCreatedAuthorizationAction extends Action
{
    public function execute(AuthorizationDTO $dto)
    {
        if ($dto->user->isAdmin()) return;

        $classBasename = class_basename($dto->authorizable);
        
        if (
            $dto->authorizable->isNotOfficial($dto->user) &&
            $dto->user->isNotAuthorizedFor(
                authorizable: $dto->authorizable,
                    name: PermissionEnum::ASSIGNAUTHORIZATIONS->value)
        ) {
            throw new AuthorizationException("Sorry! You are not authorized to perform this action on {$dto->authorizable->name} {$classBasename}.", 422);
        }
        
        if ($dto->authorizable->isNotParticipant($dto->authorized))
        {
            throw new AuthorizationException("Sorry! {$dto->authorized->name} is not participating in the {$classBasename} with name {$dto->authorizable->name}.", 422);
        }

        $classBasename = class_basename($dto->authorization);

        if (
            !$dto->user->is($dto->authorization->user) && 
            $classBasename != "Permission" &&
            !$dto->authorization->public
        ) {
            throw new AuthorizationException("Sorry! You are not authorized to perform this action on {$dto->authorization->name} role.", 422);
        }
    }
}