<?php

namespace App\Services;

use App\DTOs\LevelCollectionDTO;
use App\Exceptions\LevelCollectionException;
use App\Models\LevelCollection;
use App\Models\UserType;

class LevelCollectionService
{
    const AUTHORIZEDUSERTYPES = [
        UserType::ADMIN,
        UserType::SUPERADMIN,
        UserType::FACILITATOR,
    ];
    
    public function createLevelCollection(LevelCollectionDTO $levelCollectionDTO) : LevelCollection
    {
        if (!$levelCollectionDTO->user) {
            throw new LevelCollectionException('Sorry! A valid user is required to perform this action.');
        }

        if ($this->isNotAuthorized($levelCollectionDTO, action: 'create')) {
           throw new LevelCollectionException("Sorry! You are not authorized to create a level collection.");
        }

        if ($this->doesntHaveAppropriateData($levelCollectionDTO, action: 'create')) {
           throw new LevelCollectionException("Sorry! You need a name and value to create a level collection.");
        }

        $levelCollection = $levelCollectionDTO->user->addedLevelCollections()->create($levelCollectionDTO->getData());

        $levelCollectionDTO = $levelCollectionDTO->withLevelCollection($levelCollection);

        if (count($levelCollectionDTO->levelDTOs ?? [])) {
            $this->addLevels($levelCollectionDTO);
        }

        return $levelCollection;
    }
    
    public function updateLevelCollection(LevelCollectionDTO $levelCollectionDTO)
    {
        if (!$levelCollectionDTO->user) {
            throw new LevelCollectionException('Sorry! A valid user is required to perform this action.');
        }

        $levelCollectionDTO = $levelCollectionDTO->withLevelCollection(
            LevelCollection::find($levelCollectionDTO->levelCollectionId)
        );

        if ($this->isNotAuthorized($levelCollectionDTO, action: 'update')) {
           throw new LevelCollectionException("Sorry! You are not authorized to update a level collection.");
        }

        if (!$levelCollectionDTO->levelCollection) {
           throw new LevelCollectionException("Sorry! You need a valid level collection to perform this action.");
        }

        if ($this->doesntHaveAppropriateData($levelCollectionDTO, action: 'update')) {
           throw new LevelCollectionException("Sorry! You need a name and value to update a level collection.");
        }

        $levelCollectionDTO->levelCollection->update($this->getData($levelCollectionDTO));
        
    }

    private function hasAppropriateData(LevelCollectionDTO $levelCollectionDTO, string $action = 'create')
    {
        if ($action == 'create') {
            return !is_null($levelCollectionDTO->name) && 
                strlen($levelCollectionDTO->name) > 0 && 
                !is_null($levelCollectionDTO->value);
        }

        if ($action == 'update') {
            return (!is_null($levelCollectionDTO->name) && strlen($levelCollectionDTO->name) > 0) || 
                !is_null($levelCollectionDTO->value);
        }
        
        return !is_null($levelCollectionDTO->levelCollectionId);
    }

    private function doesntHaveAppropriateData(LevelCollectionDTO $levelCollectionDTO, string $action = 'create')
    {
        return !$this->hasAppropriateData($levelCollectionDTO, $action);
    }

    private function isAuthorized(LevelCollectionDTO $levelCollectionDTO, string $action)
    {
        if (in_array($action, ['create'])) {
            return $levelCollectionDTO->user->userTypes()->whereIn('name', self::AUTHORIZEDUSERTYPES)->exists();            
        }

        if (in_array($action, ['update', 'delete'])) {
            return $levelCollectionDTO->user->isAdmin() || $levelCollectionDTO->user->id == $levelCollectionDTO->levelCollection?->user_id;            
        }

        return false;
    }

    private function isNotAuthorized(LevelCollectionDTO $levelCollectionDTO, string $action = 'create')
    {
        return !$this->isAuthorized(
            levelCollectionDTO: $levelCollectionDTO, action: $action
        );
    }

    private function getData(LevelCollectionDTO $levelCollectionDTO) : array
    {
        $data = [];

        if ($levelCollectionDTO->name) {
            $data['name'] = $levelCollectionDTO->name;
        }

        if ($levelCollectionDTO->value) {
            $data['value'] = $levelCollectionDTO->value;
        }

        return $data;
    }

    private function addLevels(LevelCollectionDTO $levelCollectionDTO)
    {
        $levelService = new LevelService;

        for ($levelDTOIndex=0; $levelDTOIndex < $levelCollectionDTO->levelDTOs; $levelDTOIndex++) { 
            $levelDTO = $levelCollectionDTO->levelDTOs[$levelDTOIndex];

            $levelDTO = $levelDTO->withLevelCollection($levelCollectionDTO->levelCollection);

            $levelService->createLevel($levelDTO);
        }
    }
}