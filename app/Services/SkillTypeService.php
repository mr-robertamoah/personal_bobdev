<?php

namespace App\Services;

use App\DTOs\SkillDTO;
use App\DTOs\SkillTypeDTO;
use App\Exceptions\SkillTypeException;
use App\Models\SkillType;
use App\Models\UserType;

class SkillTypeService
{
    const AUTHORIZEDUSERTYPES = [
        UserType::ADMIN,
        UserType::SUPERADMIN,
        UserType::FACILITATOR
    ];

    public function createSkillType(SkillTypeDTO $skillTypeDTO)
    {
        if (!$skillTypeDTO->addedBy) {
            throw new SkillTypeException('Sorry! A valid user is required to perform this action.');
        }

        if ($this->doesntHaveAppropriateData($skillTypeDTO)) {
            throw new SkillTypeException('Sorry! The name of the skill type is required.');
        }

        if ($this->isNotAuthorized($skillTypeDTO)) {
           throw new SkillTypeException('Sorry! You are not authorized to create a skill type.');
        }

        return $skillTypeDTO->addedBy->skillTypes()->create($skillTypeDTO->getData());
    }

    public function updateSkillType(SkillTypeDTO $skillTypeDTO)
    {
        if (!$skillTypeDTO->addedBy) {
            throw new SkillTypeException('Sorry! A valid user is required to perform this action.');
        }

        if ($this->doesntHaveAppropriateData($skillTypeDTO, 'update')) {
            throw new SkillTypeException('Sorry! A skill type id is required for this operation.');
        }
        
        $skillTypeDTO = $skillTypeDTO->withSkillType(SkillType::find($skillTypeDTO->skillTypeId));

        if (!$skillTypeDTO->skillType) {
            throw new SkillTypeException("Sorry! The skill type with id {$skillTypeDTO->skillTypeId} was not found.");
        }

        if ($this->isNotAuthorized($skillTypeDTO, action: 'update')) {
           throw new SkillTypeException("Sorry! You are not authorized to update the skill type with name {$skillTypeDTO->skillType->name}.");
        }

        $result = $skillTypeDTO->skillType->update(
            $this->getData($skillTypeDTO)
        );       
        
        return $result;
    }

    public function deleteSkillType(SkillTypeDTO $skillTypeDTO)
    {
        if (!$skillTypeDTO->addedBy) {
            throw new SkillTypeException('Sorry! A valid user is required to perform this action.');
        }

        if ($this->doesntHaveAppropriateData($skillTypeDTO, 'delete')) {
            throw new SkillTypeException('Sorry! A skill type id is required for this operation.');
        }
        
        $skillTypeDTO = $skillTypeDTO->skillType ? $skillTypeDTO : $skillTypeDTO->withSkillType(SkillType::find($skillTypeDTO->skillTypeId));

        if (!$skillTypeDTO->skillType) {
            throw new SkillTypeException("Sorry! The skill type with id {$skillTypeDTO->skillTypeId} was not found.");
        }

        if ($this->isNotAuthorized($skillTypeDTO, action: 'delete')) {
           throw new SkillTypeException("Sorry! You are not authorized to delete the skill type with name {$skillTypeDTO->skillType->name}.");
        }
        
        if (
            !$skillTypeDTO->addedBy->isAdmin() &&
            $skillTypeDTO->skillType->hasSkillsAttachedToOtherJobUsers($skillTypeDTO->addedBy)
        ) {   
            return (new SkillService)->deleteSkillsBasedOnSkillTypeAndUser(
                SkillDTO::new()
                    ->fromArray([
                        'skillType' => $skillTypeDTO->skillType,
                        'user' => $skillTypeDTO->addedBy
                    ])
            );
        }
        
        (new SkillService)->deleteSkillsBasedOnSkillType(
            SkillDTO::new()->fromArray([
                'skillType' => $skillTypeDTO->skillType,
                'user' => $skillTypeDTO->addedBy
            ])
        );

        $result = $skillTypeDTO->skillType->delete();
    
        return $result;
    }

    public function getSkillType(SkillTypeDTO $skillTypeDTO)
    {
        if ($skillTypeDTO->skillTypeId) {
            return SkillType::find($skillTypeDTO->skillTypeId);
        }
        
        if ($skillTypeDTO->name) {
            return SkillType::where('name', $skillTypeDTO->name)->first();
        }
        
        return null;
    }
    
    public function getSkillTypes(SkillTypeDTO $skillTypeDTO)
    {
        return SkillType::where('name', 'LIKE', "%{$skillTypeDTO->name}%")->paginate(5);
    }

    public function attachSkillTypeToSkill(SkillTypeDTO $skillTypeDTO)
    {
        if ($this->doesntHaveAppropriateData($skillTypeDTO, 'attach')) {
            throw new SkillTypeException('Sorry! You need a valid user and skill type to perform this action.');
        }

        if (!$skillTypeDTO->attachedTo->isFacilitator()) {
            throw new SkillTypeException('Sorry! Only users that are facilitators can have skill type on this platform.');
        }

        if ($this->shouldNotAttach($skillTypeDTO)) {
            return;
        }

        $skillTypeDTO->skillType->skills()->create($skillTypeDTO->skillType->id);
    }

    public function detachSkillTypeFromUser(SkillTypeDTO $skillTypeDTO)
    {
        $this->validateSkillType($skillTypeDTO, "Sorry! There is no skill type to detach from this user.");

        $skillTypeDTO->skillType->users()->detach($skillTypeDTO->addedBy->id);
    }

    public function detachSkillTypeFromUsers(SkillTypeDTO $skillTypeDTO)
    {
        $this->validateSkillType($skillTypeDTO, "Sorry! There is no skill type to detach from users.");

        $skillTypeDTO->skillType->users()->detach();
    }

    private function validateSkillType(SkillTypeDTO $skillTypeDTO, string $message)
    {
        $skillTypeDTO = $skillTypeDTO->skillType ? $skillTypeDTO : $skillTypeDTO->withSkillType(SkillType::find($skillTypeDTO->skillTypeId));

        if (!$skillTypeDTO->skillType) {
            throw new SkillTypeException($message);
        }
    }

    private function shouldAttach(SkillTypeDTO $skillTypeDTO)
    {        
        if ($skillTypeDTO->addedBy->isAdmin() && $skillTypeDTO->attachedTo?->isFacilitator()) {
            return true;
        }
        
        if ($skillTypeDTO->addedBy->isFacilitator()) {
            return true;
        }

        return false;
    }

    private function shouldNotAttach(SkillTypeDTO $skillTypeDTO)
    {
        return !$this->shouldAttach($skillTypeDTO);
    }

    private function isAuthorized(SkillTypeDTO $skillTypeDTO, string $action)
    {
        if (in_array($action, ['create'])) {
            return $skillTypeDTO->addedBy->userTypes()->whereIn('name', self::AUTHORIZEDUSERTYPES)->exists();            
        }

        if (in_array($action, ['update', 'delete'])) {
            return $skillTypeDTO->addedBy->id == $skillTypeDTO->skillType->user_id || $skillTypeDTO->addedBy->isAdmin();
        }

        return false;
    }

    private function isNotAuthorized(SkillTypeDTO $skillTypeDTO, string $action = 'create')
    {
        return !$this->isAuthorized(
            skillTypeDTO: $skillTypeDTO, action: $action
        );
    }

    private function hasAppropriateData(SkillTypeDTO $skillTypeDTO, string $action = 'create')
    {
        if ($action == 'create') {
            return !is_null($skillTypeDTO->name) && strlen($skillTypeDTO->name) > 0;
        }

        if ($action == 'attach') {
            return !is_null($skillTypeDTO->skillType);
        }
        
        return !is_null($skillTypeDTO->skillTypeId);
    }

    private function doesntHaveAppropriateData(SkillTypeDTO $skillTypeDTO, string $action = 'create')
    {
        return !$this->hasAppropriateData($skillTypeDTO, $action);
    }

    private function getData(SkillTypeDTO $skillTypeDTO) : array
    {
        $data = [];

        if ($skillTypeDTO->name) {
            $data['name'] = $skillTypeDTO->name;
        }

        if ($skillTypeDTO->description) {
            $data['description'] = $skillTypeDTO->description;
        }

        return $data;
    }
}