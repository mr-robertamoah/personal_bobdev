<?php

namespace App\Services;

use App\DTOs\LevelCollectionDTO;
use App\Exceptions\LevelCollectionException;
use App\Models\Level;
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

        if (LevelCollection::where('name', $levelCollectionDTO->name)->exists()) {
           throw new LevelCollectionException("Sorry! A level collection already exists with the name {$levelCollectionDTO->name}.");
        }

        if (($levelMinValue = Level::MINVALUE) >= $levelCollectionDTO->value) {
           throw new LevelCollectionException("Sorry! The value of the level collection should be greater than {$levelMinValue}.");
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
            $levelCollectionDTO->levelCollection ? 
            $levelCollectionDTO->levelCollection : 
            LevelCollection::find($levelCollectionDTO->levelCollectionId)
        );

        if ($this->isNotAuthorized($levelCollectionDTO, action: 'update')) {
           throw new LevelCollectionException("Sorry! You are not authorized to update the level collection.");
        }

        if (!$levelCollectionDTO->levelCollection) {
           throw new LevelCollectionException("Sorry! You need a valid level collection to perform this action.");
        }

        if ($this->doesntHaveAppropriateData($levelCollectionDTO, action: 'update')) {
           throw new LevelCollectionException("Sorry! You need a name or value to update a level collection.");
        }

        if (
            $levelCollectionDTO->name && 
            ($levelCollectionDTO->name != $levelCollectionDTO->levelCollection->name) && 
            LevelCollection::where('name', $levelCollectionDTO->name)->exists()
        ) {
           throw new LevelCollectionException("Sorry! A level collection already exists with the name {$levelCollectionDTO->name}.");
        }

        $levelCollectionDTO->levelCollection->update($this->getData($levelCollectionDTO));
        
        return $levelCollectionDTO->levelCollection->refresh();
    }
    
    public function deleteLevelCollection(LevelCollectionDTO $levelCollectionDTO)
    {
        if (!$levelCollectionDTO->user) {
            throw new LevelCollectionException('Sorry! A valid user is required to perform this action.');
        }

        $levelCollectionDTO = $levelCollectionDTO->withLevelCollection(
            $levelCollectionDTO->levelCollection ? 
            $levelCollectionDTO->levelCollection : 
            LevelCollection::find($levelCollectionDTO->levelCollectionId)
        );

        if ($this->isNotAuthorized($levelCollectionDTO, action: 'delete')) {
           throw new LevelCollectionException("Sorry! You are not authorized to delete the level collection.");
        }

        if (!$levelCollectionDTO->levelCollection) {
           throw new LevelCollectionException("Sorry! You need a valid level collection to perform this action.");
        }
        
        $levelCollectionDTO->levelCollection->levels()->delete();

        $result = $levelCollectionDTO->levelCollection->delete();

        return $result;
    }

    public function getLevelCollection(LevelCollectionDTO $levelCollectionDTO)
    {
        if ($levelCollectionDTO->levelCollectionId) {
            return LevelCollection::find($levelCollectionDTO->levelCollectionId);
        }

        return LevelCollection::where('name', $levelCollectionDTO->name)->first();
    }
    
    public function getLevelCollections(LevelCollectionDTO $levelCollectionDTO)
    {
        return LevelCollection::where('name', 'LIKE', "%{$levelCollectionDTO->name}%")->paginate(5);
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
        
        return !is_null($levelCollectionDTO->levelCollectionId) || !is_null($levelCollectionDTO->levelCollection);
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

        for ($levelDTOIndex=0; $levelDTOIndex < count($levelCollectionDTO->levelDTOs); $levelDTOIndex++) { 
            $levelDTO = $levelCollectionDTO->levelDTOs[$levelDTOIndex];

            $levelDTO = $levelDTO->withLevelCollection($levelCollectionDTO->levelCollection);

            $levelService->createLevel($levelDTO);
        }
    }
}