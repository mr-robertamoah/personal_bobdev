<?php

namespace App\Http\Controllers;

use App\DTOs\ActivityDTO;
use App\DTOs\LevelCollectionDTO;
use App\DTOs\LevelDTO;
use App\Http\Requests\CreateLevelCollectionRequest;
use App\Http\Resources\LevelCollectionResource;
use App\Models\LevelCollection;
use App\Services\ActivityService;
use App\Services\LevelCollectionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LevelCollectionController extends Controller
{
    public function create(CreateLevelCollectionRequest $request)
    {
        DB::beginTransaction();
        
        $levelCollection = (new LevelCollectionService)->createLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => $request->get('name'),
                'value' => $request->get('value'),
                'levelDTOs' => array_map(function($level) use ($request) {
                    return LevelDTO::new()->fromArray([
                        'user' => $request->user(),
                        'name' => array_key_exists('name', $level) ? $level['name'] : null,
                        'value' => array_key_exists('value', $level) ? $level['value'] : null,
                        'description' => array_key_exists('description', $level) ? $level['description'] : null,
                    ]);
                }, is_array($request->get('levels')) ? $request->get('levels') : []),
                'user' => $request->user(),
            ])
        );

        if ($request->user()->isAdmin()) {
            (new ActivityService)->createActivity(
                ActivityDTO::new()->fromArray([
                    'performedon' => $levelCollection,
                    'performedby' => $request->user(),
                    'action' => 'create'
                ])
            );
        }

        DB::commit();
        
        return response()->json([
            'status' => true,
            'levelCollection' => new LevelCollectionResource($levelCollection)
        ]);
    }
    
    public function update(Request $request, LevelCollection $levelCollection)
    {
        DB::beginTransaction();

        $result = (new LevelCollectionService)->updateLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'name' => $request->get('name'),
                'value' => $request->get('value'),
                'user' => $request->user(),
                'levelCollectionId' => $request->level_collection_id
            ])
        );

        $levelCollection = LevelCollection::find($request->level_collection_id);
        
        if ($request->user()->isAdmin()) {
            (new ActivityService)->createActivity(
                ActivityDTO::new()->fromArray([
                    'performedon' => $levelCollection,
                    'performedby' => $request->user(),
                    'action' => 'update'
                ])
            );
        }

        DB::commit();

        return response()->json([
            'status' => (bool) $result,
            'levelCollection' => new LevelCollectionResource($levelCollection)
        ]);
    }
    
    public function delete(Request $request)
    {
        DB::beginTransaction();

        $levelCollection = LevelCollection::find($request->level_collection_id);

        if ($request->user()->isAdmin()) {
            (new ActivityService)->createActivity(
                ActivityDTO::new()->fromArray([
                    'performedon' => $levelCollection,
                    'performedby' => $request->user(),
                    'action' => 'delete'
                ])
            );
        }

        (new LevelCollectionService)->deleteLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'user' => $request->user(),
                'levelCollection' => $levelCollection
            ])
        );

        DB::commit();

        return response()->json([
            'status' => true,
        ]);
    }

    public function getLevelCollection(Request $request)
    {
        $levelCollection = (new LevelCollectionService)->getLevelCollection(
            LevelCollectionDTO::new()->fromArray([
                'levelCollectionId' => $request->query('id'),
                'name' => $request->query('name'),
            ])
        );

        return response()->json([
            'status' => true,
            'levelCollection' => $levelCollection ?
                new LevelCollectionResource($levelCollection) : 
                $levelCollection
        ]);
    }

    public function getLevelCollections(Request $request)
    {
        $levelCollections = (new LevelCollectionService)->getLevelCollections(
            LevelCollectionDTO::new()->fromArray([
                'name' => $request->query('name'),
            ])
        );

        return LevelCollectionResource::collection($levelCollections)->response()->getData(true);
    }
}
