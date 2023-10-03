<?php

namespace App\Services;

use App\Actions\Authorization\EnsureAuthorizationExistsAction;
use App\Actions\Authorization\EnsureCanDetachAuthorizationFromUserAction;
use App\Actions\EnsureUserCreatedAuthorizationAction;
use App\Actions\EnsureUserExistsAction;
use App\Actions\GetModelFromDTOAction;
use App\Actions\Permission\EnsureAuthorizationModelsExistAction;
use App\Actions\SetAuthorizationClassAction;
use App\Actions\Authorization\AttachAuthorizationsAndUsersAction;
use App\Actions\Authorization\DetachAuthorizationsAndUsersAction;
use App\Actions\Authorization\EnsureUserCanGetAuthorizationsAction;
use App\Actions\Authorization\GetAuthorizationsAction;
use App\DTOs\AuthorizationDTO;
use App\Models\Authorization;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class AuthorizationService extends Service
{
    public function attachAuthorizationsAndUsers(AuthorizationDTO $authorizationDTO) : Authorization
    {
        $authorizationDTO = $authorizationDTO->withUser(
            GetModelFromDTOAction::make()->execute($authorizationDTO)
        );

        EnsureUserExistsAction::make()->execute($authorizationDTO, "user");

        $authorizationDTO = SetAuthorizationClassAction::make()->execute(
            $authorizationDTO, "authorizableType"
        );
        
        $authorizationDTO = SetAuthorizationClassAction::make()->execute(
            $authorizationDTO, "authorizationType"
        );
        
        $authorizationDTO = $authorizationDTO->withAuthorizable(
            GetModelFromDTOAction::make()->execute(
                $authorizationDTO, "authorizable", $authorizationDTO->authorizableType
            )
        );

        $authorizationDTO = $authorizationDTO->withAuthorized(
            GetModelFromDTOAction::make()->execute(
                $authorizationDTO, "authorized"
            )
        );

        $authorizationDTO = $authorizationDTO->withAuthorization(
            GetModelFromDTOAction::make()->execute(
                $authorizationDTO, "authorization", $authorizationDTO->authorizationType
            )
        );

        EnsureAuthorizationModelsExistAction::make()->execute($authorizationDTO);

        EnsureUserCreatedAuthorizationAction::make()->execute($authorizationDTO);

        return AttachAuthorizationsAndUsersAction::make()->execute($authorizationDTO);
    }
    
    public function detachAuthorizationsAndUsers(AuthorizationDTO $authorizationDTO) : bool
    {
        $authorizationDTO = $authorizationDTO->withUser(
            GetModelFromDTOAction::make()->execute($authorizationDTO)
        );

        EnsureUserExistsAction::make()->execute($authorizationDTO, "user");

        $authorizationDTO = $authorizationDTO->withMainAuthorization(
            GetModelFromDTOAction::make()->execute(
                $authorizationDTO, "mainAuthorization", "authorization"
            )
        );

        EnsureAuthorizationExistsAction::make()->execute($authorizationDTO);

        EnsureCanDetachAuthorizationFromUserAction::make()->execute($authorizationDTO);

        return DetachAuthorizationsAndUsersAction::make()->execute($authorizationDTO);
    }
    
    public function getAuthorizations(AuthorizationDTO $authorizationDTO) : LengthAwarePaginator
    {
        $authorizationDTO = $authorizationDTO->withUser(
            GetModelFromDTOAction::make()->execute($authorizationDTO)
        );

        EnsureUserExistsAction::make()->execute($authorizationDTO, "user");

        $authorizationDTO = SetAuthorizationClassAction::make()->execute(
            $authorizationDTO, "authorizableType"
        );
        
        $authorizationDTO = SetAuthorizationClassAction::make()->execute(
            $authorizationDTO, "authorizationType"
        );
        
        $authorizationDTO = $authorizationDTO->withAuthorizable(
            GetModelFromDTOAction::make()->execute(
                $authorizationDTO, "authorizable", $authorizationDTO->authorizableType
            )
        );

        $authorizationDTO = $authorizationDTO->withAuthorized(
            GetModelFromDTOAction::make()->execute(
                $authorizationDTO, "authorized"
            )
        );

        $authorizationDTO = $authorizationDTO->withAuthorization(
            GetModelFromDTOAction::make()->execute(
                $authorizationDTO, "authorization", $authorizationDTO->authorizationType
            )
        );

        EnsureAuthorizationModelsExistAction::make()->execute(
            $authorizationDTO, 
            $authorizationDTO->user->isAdmin() ||
            $authorizationDTO->isForNextPage() ? "" : "authorizable"
        );

        EnsureUserCanGetAuthorizationsAction::make()->execute($authorizationDTO);

        return GetAuthorizationsAction::make()->execute($authorizationDTO);
    }
}