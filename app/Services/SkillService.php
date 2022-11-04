<?php

namespace App\Services;

use App\DTOs\SkillDTO;
use App\Exceptions\SkillException;
use App\Models\Skill;
use App\Models\SkillType;
use App\Models\UserType;

class SkillService
{
    const AUTHORIZEDUSERTYPES = [
        UserType::ADMIN,
        UserType::SUPERADMIN,
        UserType::FACILITATOR
    ];

    public function createSkill(SkillDTO $skillDTO)
    {
        if (!$skillDTO->user) {
            throw new SkillException('Sorry! A valid user is required to perform this action.');
        }

        if ($this->isNotAuthorized($skillDTO)) {
           throw new SkillException('Sorry! You are not authorized to create a skill.');
        }

        if (!SkillType::find($skillDTO->skillTypeId)) {
            throw new SkillException('Sorry! A valid skill type is required to perform this action.');
        }

        if ($this->doesntHaveAppropriateData($skillDTO)) {
            throw new SkillException("Sorry! The name and description of the skill and user's id are required.");
        }

        return $skillDTO->user->addedSkills()->create([
            'name' => $skillDTO->name,
            'description' => $skillDTO->description,
            'skill_type_id' => $skillDTO->skillTypeId,
        ]);
    }

    public function updateSkill(SkillDTO $skillDTO)
    {
        if (!$skillDTO->user) {
            throw new SkillException('Sorry! A valid user is required to perform this action.');
        }
        
        $skillDTO = $skillDTO->withSkill(Skill::find($skillDTO->skillId));

        if ($this->isNotAuthorized($skillDTO, 'update')) {
           throw new SkillException('Sorry! You are not authorized to update a skill.');
        }
        
        if ($this->doesntHaveAppropriateData($skillDTO, 'update')) {
            throw new SkillException("Sorry! The name and description of the skill and user's id are required.");
        }

        if (!$skillDTO->skill) {
            throw new SkillException('Sorry! A valid skill is required to perform this action.');
        }

        $skillDTO->skill->update($this->getData($skillDTO));

        return $skillDTO->skill->refresh();
    }

    public function deleteSkill(SkillDTO $skillDTO)
    {
        if (!$skillDTO->user) {
            throw new SkillException('Sorry! A valid user is required to perform this action.');
        }
        
        $skillDTO = $skillDTO->withSkill(Skill::find($skillDTO->skillId));

        if ($this->isNotAuthorized($skillDTO, 'delete')) {
           throw new SkillException('Sorry! You are not authorized to delete a skill.');
        }
        
        if ($this->doesntHaveAppropriateData($skillDTO, 'delete')) {
            throw new SkillException('Sorry! A valid skill is required to perform this action.');
        }

        if ($skillDTO->user->isAdmin()) {
            $skillDTO->skill->jobUserSkills()->delete();
        }
        
        if (
            !$skillDTO->user->isAdmin() &&
            $skillDTO->skill->jobUserSkills()->whereNotUser($skillDTO->user)->exists()
        ) {
            $skillDTO->skill->jobUserSkills()->whereUser($skillDTO->user)
                ->where('skill_id', $skillDTO->skill->id)->delete();
                
            return 1;
        }

        return $skillDTO->skill->delete();
    }
    
    public function deleteSkillsBasedOnSkillType(SkillDTO $skillDTO) : bool
    {
        if (!$skillDTO->skillType) {
            throw new SkillException('Sorry! A skill type is required to perform this action');
        }
        
        if (!$skillDTO->user?->isAdmin() && $skillDTO->user?->id != $skillDTO->skillType->user_id) {
            throw new SkillException('Sorry! You do not have permission to perform this action.');
        }

        $result = $skillDTO->skillType->skills()->delete();

        return (bool) $result;
    }
    
    public function deleteSkillsBasedOnSkillTypeAndUser(SkillDTO $skillDTO) : bool
    {
        if (!$skillDTO->skillType || !$skillDTO->user) {
            throw new SkillException('Sorry! A skill type and user are required to perform this action');
        }
        
        if (!$skillDTO->user?->isFacilitator()) {
            throw new SkillException('Sorry! You have to be a facilitator to perform this action.');
        }
        
        $result = $skillDTO->skillType->skills()->whereAddedBy($skillDTO->user)->delete();

        return (bool) $result;
    }

    public function getSkill(SkillDTO $skillDTO)
    {
        if ($skillDTO->skillId) {
            return Skill::find($skillDTO->skillId);
        }

        return Skill::where('name', $skillDTO->name)->first();
    }
    
    public function getSkills(SkillDTO $skillDTO)
    {
        return Skill::where('name', 'LIKE', "%{$skillDTO->name}%")->paginate(5);
    }

    private function isAuthorized(SkillDTO $skillDTO, string $action)
    {
        if (in_array($action, ['create'])) {
            return $skillDTO->user->userTypes()->whereIn('name', self::AUTHORIZEDUSERTYPES)->exists();            
        }

        if (in_array($action, ['update', 'delete'])) {
            return $skillDTO->user->id == $skillDTO->skill?->user_id || $skillDTO->user->isAdmin();
        }

        return false;
    }

    private function isNotAuthorized(SkillDTO $skillDTO, string $action = 'create')
    {
        return !$this->isAuthorized(
            skillDTO: $skillDTO, action: $action
        );
    }

    private function hasAppropriateData(SkillDTO $skillDTO, string $action = 'create')
    {
        if ($action == 'create') {
            return (!is_null($skillDTO->name) && strlen($skillDTO->name) > 0) && 
                (!is_null($skillDTO->description) && strlen($skillDTO->description) > 0) && 
                !is_null($skillDTO->user);
        }

        if ($action == 'update') {
            return ((!is_null($skillDTO->name) && strlen($skillDTO->name) > 0) || 
                (!is_null($skillDTO->description) && strlen($skillDTO->description) > 0)) && 
                !is_null($skillDTO->user);
        }
        
        return !is_null($skillDTO->skillId);
    }

    private function doesntHaveAppropriateData(SkillDTO $skillDTO, string $action = 'create')
    {
        return !$this->hasAppropriateData($skillDTO, $action);
    }

    private function getData(SkillDTO $skillDTO) : array
    {
        $data = [];

        if ($skillDTO->name) {
            $data['name'] = $skillDTO->name;
        }

        if ($skillDTO->description) {
            $data['description'] = $skillDTO->description;
        }

        return $data;
    }
}