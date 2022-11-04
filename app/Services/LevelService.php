<?php

namespace App\Services;

use App\DTOs\LevelDTO;
use App\Exceptions\LevelException;
use App\Models\JobUser;
use App\Models\JobUserSkill;
use App\Models\Level;
use App\Models\LevelCollection;
use App\Models\Skill;
use App\Models\UserType;
use Illuminate\Database\Eloquent\Collection;

class LevelService
{
    const AUTHORIZEDUSERTYPES = [
        UserType::ADMIN,
        UserType::SUPERADMIN,
        UserType::FACILITATOR,
    ];
    
    public function createLevel(LevelDTO $levelDTO)
    {
        if (!$levelDTO->user) {
            throw new LevelException('Sorry! A valid user is required to perform this action.');
        }

        if ($this->isNotAuthorized($levelDTO, action: 'create')) {
           throw new LevelException("Sorry! You are not authorized to create a level.");
        }

        if ($this->doesntHaveAppropriateData($levelDTO, action: 'create')) {
           throw new LevelException("Sorry! You need a name and value to create a level.");
        }

        $levelDTO = $levelDTO->levelCollection ? $levelDTO : $levelDTO->withLevelCollection(
            LevelCollection::find($levelDTO->levelCollectionId)
        );

        if (!$levelDTO->levelCollection) {
           throw new LevelException("Sorry! A valid level collection id is required to create a level.");
        }

        if ($levelDTO->levelCollection->hasLevelWithName($levelDTO->name)) {
           throw new LevelException("Sorry! A level with name {$levelDTO->name} already exists on this collection.");
        }

        if ($this->valueNotInRange($levelDTO)) {
            $min = Level::MINVALUE;

            throw new LevelException("Sorry! A level's value for this collection should be between {$min} and {$levelDTO->levelCollection->value}.");
        }

        return $levelDTO->user->addedLevels()->create(array_merge($levelDTO->getData(), [
            'level_collection_id' => $levelDTO->levelCollection ? $levelDTO->levelCollection->id : $levelDTO->levelCollectionId
        ]));
        
    }
    
    public function updateLevel(LevelDTO $levelDTO)
    {
        if (!$levelDTO->user) {
            throw new LevelException('Sorry! A valid user is required to perform this action.');
        }

        $levelDTO = $levelDTO->level ? $levelDTO : $levelDTO->withLevel(
            Level::find($levelDTO->levelId)
        );

        if (!$levelDTO->level) {
           throw new LevelException("Sorry! You need a valid level to perform this action.");
        }

        if ($this->isNotAuthorized($levelDTO, action: 'update')) {
           throw new LevelException("Sorry! You are not authorized to update the level.");
        }

        if ($this->doesntHaveAppropriateData($levelDTO, action: 'update')) {
           throw new LevelException("Sorry! You need a name, value or description to update the level.");
        }

        $levelDTO = $levelDTO->withLevelCollection(
            $levelDTO->level->levelCollection
        );

        if ($levelDTO->name && $levelDTO->levelCollection->hasLevelWithName($levelDTO->name)) {
           throw new LevelException("Sorry! A level with name {$levelDTO->name} already exists on this collection.");
        }

        if (!is_null($levelDTO->value) && $this->valueNotInRange($levelDTO)) {
            $min = Level::MINVALUE;

            throw new LevelException("Sorry! A level's value for this collection should be between {$min} and {$levelDTO->levelCollection->value}.");
        }

        $levelDTO->level->update($this->getData($levelDTO));
        
        $levelDTO->level->refresh();

        return $levelDTO->level;
    }
    
    public function deleteLevel(LevelDTO $levelDTO)
    {
        if (!$levelDTO->user) {
            throw new LevelException('Sorry! A valid user is required to perform this action.');
        }

        $levelDTO = $levelDTO->level ? $levelDTO : $levelDTO->withLevel(
            Level::find($levelDTO->levelId)
        );

        if (!$levelDTO->level) {
           throw new LevelException("Sorry! You need a valid level to perform this action.");
        }

        if ($this->isNotAuthorized($levelDTO, action: 'update')) {
           throw new LevelException("Sorry! You are not authorized to update the level.");
        }
        
        if (
            !$levelDTO->user->isAdmin() &&
            JobUserSkill::whereLevel($levelDTO->level->id)->whereNotUser($levelDTO->user)->exists()
        ) {
            JobUserSkill::whereLevel($levelDTO->level->id)->whereUser($levelDTO->user)->update(['level_id' => null]);
    
            return true;
        }

        JobUserSkill::whereLevel($levelDTO->level->id)->update(['level_id' => null]);
        
        $result = $levelDTO->level->delete();
    
        return (bool) $result;
    }

    public function getLevel(LevelDTO $levelDTO)
    {
        if ($levelDTO->levelId) {
            return Level::find($levelDTO->levelId);
        }

        return Level::where('name', $levelDTO->name)->first();
    }
    
    public function getLevels(LevelDTO $levelDTO)
    {
        return Level::where('name', 'LIKE', "%{$levelDTO->name}%")->paginate(5);
    }

    private function hasAppropriateData(LevelDTO $levelDTO, string $action = 'create')
    {
        if ($action == 'create') {
            return (!is_null($levelDTO->name) && strlen($levelDTO->name) > 0) && 
                !is_null($levelDTO->value);
        }

        if ($action == 'update') {
            return (!is_null($levelDTO->name) && strlen($levelDTO->name) > 0) || 
                (!is_null($levelDTO->description) && strlen($levelDTO->description) > 0) || 
                !is_null($levelDTO->value);
        }
        
        return !is_null($levelDTO->levelId) || !is_null($levelDTO->level);
    }

    private function doesntHaveAppropriateData(LevelDTO $levelDTO, string $action = 'create')
    {
        return !$this->hasAppropriateData($levelDTO, $action);
    }

    private function isAuthorized(LevelDTO $levelDTO, string $action)
    {
        if (in_array($action, ['create'])) {
            return $levelDTO->user->userTypes()->whereIn('name', self::AUTHORIZEDUSERTYPES)->exists();            
        }
        
        if (in_array($action, ['update', 'delete'])) {
            return $levelDTO->user->isAdmin() || $levelDTO->user->id == $levelDTO->level?->user_id;            
        }

        return false;
    }

    private function isNotAuthorized(LevelDTO $levelDTO, string $action = 'create')
    {
        return !$this->isAuthorized(
            levelDTO: $levelDTO, action: $action
        );
    }

    private function getData(LevelDTO $levelDTO) : array
    {
        $data = [];

        if ($levelDTO->name) {
            $data['name'] = $levelDTO->name;
        }

        if ($levelDTO->description) {
            $data['description'] = $levelDTO->description;
        }

        if ($levelDTO->value) {
            $data['value'] = $levelDTO->value;
        }

        return $data;
    }

    private function valueInRange(LevelDTO $levelDTO)
    {
        if (
            $levelDTO->value < Level::MINVALUE || 
            $levelDTO->value > $levelDTO->levelCollection->value
        ) {
            return false;
        }

        return true;
    }

    private function valueNotInRange(LevelDTO $levelDTO)
    {
        return !$this->valueInRange($levelDTO);
    }
}