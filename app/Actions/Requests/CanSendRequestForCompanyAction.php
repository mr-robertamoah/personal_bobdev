<?php

namespace App\Actions\Requests;

use App\Actions\Action;
use App\Actions\Company\EnsureIsRightCompanyRelationshipAction;
use App\Actions\Company\EnsureRequestIsNotFromACompanyOfficialToAnotherAction;
use App\Actions\Company\EnsureSenderOrRecepientIsOfficialOfCompanyAction;
use App\Actions\Company\EnsureUserCanAddMemberAction;
use App\Actions\Company\EnsureUserIsAnAdultIfAdministratorRelationshipTypeAction;
use App\Actions\Company\EnsureUserIsNotAlreadyAMemberOfCompanyAction;
use App\DTOs\CompanyDTO;
use App\DTOs\RequestDTO;
use App\Exceptions\RequestException;
use App\Models\User;
use Exception;

class CanSendRequestForCompanyAction extends Action
{
    private ?User $companyOfficial = null;
    private ?User $otherUser = null;

    public function execute(RequestDTO $requestDTO)
    {
        $this->setOtherUser($requestDTO);

        try {

            EnsureIsRightCompanyRelationshipAction::make()->execute($requestDTO->type, $this->otherUser);

            EnsureRequestIsNotFromACompanyOfficialToAnotherAction::make()->execute($requestDTO);

            EnsureSenderOrRecepientIsOfficialOfCompanyAction::make()->execute($requestDTO);
    
            EnsureUserIsAnAdultIfAdministratorRelationshipTypeAction::make()->execute(
                $this->otherUser, $requestDTO->type
            );
    
            EnsureUserIsNotAlreadyAMemberOfCompanyAction::make()->execute(
                $requestDTO->for, $this->otherUser
            );
    
            EnsureUserCanAddMemberAction::make()->execute(
                CompanyDTO::new()->fromArray([
                    'user' => $this->companyOfficial,
                    'company' => $requestDTO->for,
                    'relationshipType' => $requestDTO->type
                ]),
                true
            );
        } catch (Exception $e) {

            throw new RequestException($e->getMessage());
        }
    }

    private function setOtherUser(RequestDTO $requestDTO)
    {
        if ($requestDTO->for->isOfficial($requestDTO->from)) {
            
            $this->companyOfficial = $requestDTO->from;
            $this->otherUser = $requestDTO->to;

            return;
        }
            
        // if it is not sent to a specific official then have it sent to owner
        $this->companyOfficial = $requestDTO->to ? $requestDTO->to : $requestDTO->for->owner;
        $this->otherUser = $requestDTO->from;
    }
}