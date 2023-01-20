<?php

namespace App\Services;

use App\Actions\Activity\AddActivityAction;
use App\Actions\Company\BecomeCompanyMemberAction;
use App\Actions\Company\EnsureUserCanAddMemberAction;
use App\Actions\Company\EnsureCompanyExistsAction;
use App\Actions\Company\EnsureHasDataToCreateCompanyAction;
use App\Actions\Company\EnsureHasDataToUpdateCompanyAction;
use App\Actions\Company\EnsureIsRightCompanyRelationshipAction;
use App\Actions\Company\EnsureMembershipIsListAction;
use App\Actions\Company\EnsureUserIsOfficialOfCompanyAction;
use App\Actions\Company\EnsureThereIsAnAppriopriateUserAction;
use App\Actions\Company\EnsureUserCanRemoveMemberAction;
use App\Actions\Company\EnsureUserIsAlreadyAMemberOfCompanyAction;
use App\Actions\Company\EnsureUserIsAnAdultIfAdministratorRelationshipTypeAction;
use App\Actions\Company\EnsureUserIsNotAlreadyAMemberOfCompanyAction;
use App\Actions\Company\EnsureUserIsOwnerOfCompanyAction;
use App\Actions\Company\RemoveMemberAction;
use App\Actions\Users\EnsureThereIsUserOnDTOAction;
use App\Actions\Users\EnsureUserIsAnAdultAction;
use App\Actions\Users\FindUserByIdAction;
use App\DTOs\ActivityDTO;
use App\DTOs\CompanyDTO;
use App\Models\Company;
use App\Models\User;

class CompanyService
{

    public function createCompany(CompanyDTO $companyDTO): Company
    {
        $companyDTO = $this->setUserOnCompanyDTO($companyDTO);

        $companyDTO = $this->setOwnerOnCompanyDTO($companyDTO);

        EnsureThereIsAnAppriopriateUserAction::make()->execute($companyDTO);

        $creator = $this->getCreator($companyDTO);

        EnsureUserIsAnAdultAction::make()->execute($creator);

        EnsureHasDataToCreateCompanyAction::make()->execute($companyDTO);

        $company = $creator->addedCompanies()->create(
            $this->setData($companyDTO, 'create')
        );
    
        AddActivityAction::make()->execute(
            ActivityDTO::new()->fromArray([
                'performedby' => $creator,
                'performedon' => $company,
                'action' => 'create',
                'data' => ['user' => $companyDTO->user]
            ])
        );

        return $company;
    }

    public function updateCompany(CompanyDTO $companyDTO): Company
    {
        $companyDTO = $this->setUserOnCompanyDTO($companyDTO);

        EnsureThereIsUserOnDTOAction::make()->execute($companyDTO, 'user');

        $companyDTO = $this->setCompanyOnCompanyDTO($companyDTO);

        EnsureCompanyExistsAction::make()->execute($companyDTO);

        EnsureUserIsOfficialOfCompanyAction::make()->execute($companyDTO);

        EnsureHasDataToUpdateCompanyAction::make()->execute($companyDTO);

        $companyDTO->company->update(
            $this->setData($companyDTO, 'update')
        );

        AddActivityAction::make()->execute(
            ActivityDTO::new()->fromArray([
                'performedby' => $companyDTO->user,
                'performedon' => $companyDTO->company,
                'action' => 'update',
            ])
        );

        return $companyDTO->company->refresh();
    }

    public function deleteCompany(CompanyDTO $companyDTO): bool
    {
        $companyDTO = $this->setUserOnCompanyDTO($companyDTO);

        EnsureThereIsUserOnDTOAction::make()->execute($companyDTO, 'user');

        $companyDTO = $this->setCompanyOnCompanyDTO($companyDTO);

        EnsureCompanyExistsAction::make()->execute($companyDTO);

        EnsureUserIsOwnerOfCompanyAction::make()->execute(
            $companyDTO->company,
            $companyDTO->user
        );

        AddActivityAction::make()->execute(
            ActivityDTO::new()->fromArray([
                'performedby' => $companyDTO->user,
                'performedon' => $companyDTO->company,
                'action' => 'delete',
            ])
        );

        return $companyDTO->company->delete();
    }

    public function addMembers(CompanyDTO $companyDTO)
    {
        $companyDTO = $this->setUserOnCompanyDTO($companyDTO);

        EnsureThereIsUserOnDTOAction::make()->execute($companyDTO, 'user');

        $companyDTO = $this->setCompanyOnCompanyDTO($companyDTO);

        EnsureCompanyExistsAction::make()->execute($companyDTO);

        EnsureUserIsOfficialOfCompanyAction::make()->execute($companyDTO);

        EnsureMembershipIsListAction::make()->execute($companyDTO);

        foreach ($companyDTO->memberships as $memberId => $relationshipType) {
            
            $member = FindUserByIdAction::make()->execute($memberId);

            EnsureUserCanAddMemberAction::make()->execute(
                $companyDTO->withRelationshipType($relationshipType)
            );

            EnsureIsRightCompanyRelationshipAction::make()->execute($relationshipType, $member);

            EnsureUserIsAnAdultIfAdministratorRelationshipTypeAction::make()->execute(
                $member, $relationshipType
            );

            EnsureUserIsNotAlreadyAMemberOfCompanyAction::make()->execute(
                $companyDTO->company, $member
            );

            BecomeCompanyMemberAction::make()->execute(
                $companyDTO->company,
                $member,
                $relationshipType
            );
    
            AddActivityAction::make()->execute(
                ActivityDTO::new()->fromArray([
                    'performedby' => $companyDTO->user,
                    'performedon' => $companyDTO->company,
                    'action' => 'addMember',
                    'data' => ['user' => $member]
                ])
            );
        }

        return $companyDTO->company->refresh();
    }

    public function removeMembers(CompanyDTO $companyDTO)
    {
        $companyDTO = $this->setUserOnCompanyDTO($companyDTO);

        EnsureThereIsUserOnDTOAction::make()->execute($companyDTO, 'user');

        $companyDTO = $this->setCompanyOnCompanyDTO($companyDTO);

        EnsureCompanyExistsAction::make()->execute($companyDTO);

        EnsureUserIsOfficialOfCompanyAction::make()->execute($companyDTO);

        EnsureMembershipIsListAction::make()->execute($companyDTO);
        
        foreach($companyDTO->memberships as $memberId => $relationshipType) {
            
            $member = FindUserByIdAction::make()->execute($memberId);

            EnsureIsRightCompanyRelationshipAction::make()->execute($relationshipType, $member);
            
            EnsureUserIsAlreadyAMemberOfCompanyAction::make()->execute(
                $companyDTO->company, $member, $relationshipType
            );

            EnsureUserCanRemoveMemberAction::make()->execute($companyDTO, $member);

            RemoveMemberAction::make()->execute(
                $companyDTO->company,
                $member
            );

            AddActivityAction::make()->execute(
                ActivityDTO::new()->fromArray([
                    'performedby' => $companyDTO->user,
                    'performedon' => $companyDTO->company,
                    'action' => 'removeMember',
                    'data' => [
                        'user' => $member
                    ]
                ])
            );
        }

        return $companyDTO->company->refresh();
    }

    public function leave(CompanyDTO $companyDTO)
    {
        $companyDTO = $this->setUserOnCompanyDTO($companyDTO);

        EnsureThereIsUserOnDTOAction::make()->execute($companyDTO, 'user');

        $companyDTO = $this->setCompanyOnCompanyDTO($companyDTO);

        EnsureCompanyExistsAction::make()->execute($companyDTO);

        EnsureIsRightCompanyRelationshipAction::make()->execute($companyDTO->relationshipType, $companyDTO->user);
        
        EnsureUserIsAlreadyAMemberOfCompanyAction::make()->execute(
            $companyDTO->company, $companyDTO->user, $companyDTO->relationshipType
        );

        EnsureUserCanRemoveMemberAction::make()->execute($companyDTO, $companyDTO->user);

        RemoveMemberAction::make()->execute(
            $companyDTO->company,
            $companyDTO->user
        );

        AddActivityAction::make()->execute(
            ActivityDTO::new()->fromArray([
                'performedby' => $companyDTO->user,
                'performedon' => $companyDTO->company,
                'action' => 'removeMember',
                'data' => [
                    'user' => $companyDTO->user
                ]
            ])
        );

        return $companyDTO->company->refresh();
    }

    private function setData(CompanyDTO $companyDTO, string $action): array
    {
        $data = [];

        if ($companyDTO->name && in_array($action, ['create', 'update'])) {
            $data['name'] = $companyDTO->name;
        }

        if ($companyDTO->alias && in_array($action, ['create'])) {
            $data['alias'] = $companyDTO->alias;
        }

        if ($companyDTO->about && in_array($action, ['create', 'update'])) {
            $data['about'] = $companyDTO->about;
        }

        return $data;
    }

    private function setUserOnCompanyDTO(CompanyDTO $companyDTO): CompanyDTO
    {
        return $companyDTO->user ? $companyDTO : $companyDTO->withUser(
           User::find( $companyDTO->userId)
        );
    }

    private function setOwnerOnCompanyDTO(CompanyDTO $companyDTO): CompanyDTO
    {
        return $companyDTO->owner ? $companyDTO : $companyDTO->withOwner(
           User::find( $companyDTO->ownerId)
        );
    }

    private function setCompanyOnCompanyDTO(CompanyDTO $companyDTO): CompanyDTO
    {
        return $companyDTO->company ? $companyDTO : $companyDTO->withCompany(
            Company::find($companyDTO->companyId)
        );
    }

    private function getCreator(CompanyDTO $companyDTO): User
    {
        if (is_null($companyDTO->owner)) {
            return $companyDTO->user;
        }

        return $companyDTO->owner;
    }
}