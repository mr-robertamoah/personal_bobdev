<?php

namespace App\Services;

use App\DTOs\LevelDTO;
use App\Exceptions\LevelException;
use App\Models\LevelCollection;
use App\Models\UserType;

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

        if (!LevelCollection::find($levelDTO->levelCollectionId)) {
           throw new LevelException("Sorry! A valid level collection id is required to create a level.");
        }

        $levelDTO->user->addedLevels()->create(array_merge($levelDTO->getData(), [
            'level_collection_id' => $levelDTO->levelCollectionId
        ]));
        
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
}