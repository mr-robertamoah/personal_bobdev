<?php

namespace App\Services;

use App\Actions\Activity\AddActivityAction;
use App\Actions\Company\EnsureUserCanAddMemberAction;
use App\Actions\Company\EnsureCompanyExistsAction;
use App\Actions\Company\EnsureHasDataToCreateCompanyAction;
use App\Actions\Company\EnsureHasDataToUpdateCompanyAction;
use App\Actions\Company\EnsureIsRightCompanyRelationshipAction;
use App\Actions\Company\EnsureMembershipIsValidArrayAction;
use App\Actions\Company\EnsureRequestIsNotFromACompanyOfficialToAnotherAction;
use App\Actions\Company\EnsureUserIsOfficialOfCompanyAction;
use App\Actions\Company\EnsureThereIsAnAppriopriateUserAction;
use App\Actions\Company\EnsureTypeIsValidAction;
use App\Actions\Company\EnsureUserCanRemoveMemberAction;
use App\Actions\Company\EnsureUserIsAlreadyAMemberOfCompanyAction;
use App\Actions\Company\EnsureUserIsAnAdultIfAdministratorRelationshipTypeAction;
use App\Actions\Company\EnsureUserIsNotAlreadyAMemberOfCompanyAction;
use App\Actions\Company\EnsureUserIsOwnerOfCompanyAction;
use App\Actions\Company\GetCompaniesAction;
use App\Actions\Company\GetCompanyAction;
use App\Actions\Company\GetMembersAction;
use App\Actions\Company\GetProjectsAction;
use App\Actions\Company\RemoveMemberAction;
use App\Actions\GetModelFromDTOAction;
use App\Actions\GetUserDataForUserId;
use App\Actions\Requests\CreateRequestAction;
use App\Actions\Users\EnsureThereIsUserOnDTOAction;
use App\Actions\Users\EnsureUserIsAnAdultAction;
use App\Actions\Users\FindUserByIdAction;
use App\DTOs\ActivityDTO;
use App\DTOs\CompanyDTO;
use App\DTOs\RequestDTO;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class CompanyService extends Service
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

    // TODO add an addMember function that just adds members and can only be used by system admins
    
    public function sendMembershipRequest(CompanyDTO $companyDTO)
    {
        $companyDTO = $this->setUserOnCompanyDTO($companyDTO);

        EnsureThereIsUserOnDTOAction::make()->execute($companyDTO, 'user');

        $companyDTO = $this->setCompanyOnCompanyDTO($companyDTO);

        EnsureCompanyExistsAction::make()->execute($companyDTO);

        EnsureUserIsOfficialOfCompanyAction::make()->execute($companyDTO);

        EnsureMembershipIsValidArrayAction::make()->execute($companyDTO);

        $requests = [];
        foreach ($companyDTO->memberships as $userId => $userData)
        {
            [$relationshipType, $purpose] = GetUserDataForUserId::make()->execute($userData);

            $possibleMember = FindUserByIdAction::make()->execute($userId);

            EnsureIsRightCompanyRelationshipAction::make()->execute($relationshipType, $possibleMember);

            EnsureRequestIsNotFromACompanyOfficialToAnotherAction::make()->execute(
                $companyDTO->withTo($possibleMember)
            );

            EnsureUserIsAnAdultIfAdministratorRelationshipTypeAction::make()->execute(
                $possibleMember, $relationshipType
            );

            EnsureUserIsNotAlreadyAMemberOfCompanyAction::make()->execute(
                $companyDTO->company, $possibleMember
            );

            EnsureUserCanAddMemberAction::make()->execute(
                $companyDTO->withRelationshipType($relationshipType)
            );

            $requests[] = CreateRequestAction::make()->execute(
                RequestDTO::new()->fromArray([
                    'from' => $companyDTO->user,
                    'for' => $companyDTO->company,
                    'to' => $possibleMember,
                    'type' => strtoupper($relationshipType),
                    'purpose' => $purpose
                ])
            );
        }

        AddActivityAction::make()->execute(
            ActivityDTO::new()->fromArray([
                'performedby' => $companyDTO->user,
                'performedon' => $companyDTO->company,
                'action' => 'removeMember',
                'data' => [
                    'requests' => array_map(fn($request) => $request->id, $requests)
                ]
            ])
        );

        return $requests;
    }

    public function removeMembers(CompanyDTO $companyDTO)
    {
        $companyDTO = $this->setUserOnCompanyDTO($companyDTO);

        EnsureThereIsUserOnDTOAction::make()->execute($companyDTO, 'user');

        $companyDTO = $this->setCompanyOnCompanyDTO($companyDTO);

        EnsureCompanyExistsAction::make()->execute($companyDTO);

        EnsureUserIsOfficialOfCompanyAction::make()->execute($companyDTO);

        EnsureMembershipIsValidArrayAction::make()->execute($companyDTO);
        
        foreach($companyDTO->memberships as $memberId => $userData)
        {
            [$relationshipType] = GetUserDataForUserId::make()->execute($userData);

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

    public function getCompanies(CompanyDTO $companyDTO) : LengthAwarePaginator
    {
        $companyDTO = $companyDTO->withOwner(
            GetModelFromDTOAction::make()->execute(
                $companyDTO, "owner"
            )
        );
        
        $companyDTO = $companyDTO->withOfficial(
            GetModelFromDTOAction::make()->execute(
                $companyDTO, "official"
            )
        );
        
        $companyDTO = $companyDTO->withMember(
            GetModelFromDTOAction::make()->execute(
                $companyDTO, "member"
            )
        );

        return GetCompaniesAction::make()->execute($companyDTO);
    }

    public function getCompany(CompanyDTO $companyDTO) : Company
    {
        $companyDTO = $companyDTO->withCompany(
            GetModelFromDTOAction::make()->execute(
                $companyDTO, "company", "company"
            )
        );
        
        EnsureCompanyExistsAction::make()->execute($companyDTO);

        return GetCompanyAction::make()->execute($companyDTO);
    }

    public function getMembers(CompanyDTO $companyDTO) : Collection
    {
        EnsureTypeIsValidAction::make()->execute($companyDTO);

        $companyDTO = $companyDTO->withCompany(
            GetModelFromDTOAction::make()->execute(
                $companyDTO, "company", "company"
            )
        );
        
        EnsureCompanyExistsAction::make()->execute($companyDTO);

        return GetMembersAction::make()->execute($companyDTO);
    }

    public function getCompanyProjects(CompanyDTO $companyDTO) : Collection
    {
        EnsureTypeIsValidAction::make()->execute($companyDTO, "projects");

        $companyDTO = $companyDTO->withCompany(
            GetModelFromDTOAction::make()->execute(
                $companyDTO, "company", "company"
            )
        );
        
        EnsureCompanyExistsAction::make()->execute($companyDTO);

        return GetProjectsAction::make()->execute($companyDTO);
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