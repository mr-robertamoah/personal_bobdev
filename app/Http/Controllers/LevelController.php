<?php

namespace App\Http\Controllers;

use App\DTOs\ActivityDTO;
use App\DTOs\LevelDTO;
use App\Http\Requests\CreateLevelRequest;
use App\Http\Resources\LevelResource;
use App\Models\Level;
use App\Services\ActivityService;
use App\Services\LevelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class LevelController extends Controller
{
    public function create(CreateLevelRequest $request)
    {
        DB::beginTransaction();
        
        $level = (new LevelService)->createLevel(
            LevelDTO::new()->fromArray([
                'name' => $request->get('name'),
                'value' => $request->get('value'),
                'description' => $request->get('description'),
                'levelCollectionId' => $request->get('level_collection_id'),
                'user' => $request->user(),
            ])
        );

        if ($request->user()->isAdmin()) {
            (new ActivityService)->createActivity(
                ActivityDTO::new()->fromArray([
                    'performedon' => $level,
                    'performedby' => $request->user(),
                    'action' => 'create'
                ])
            );
        }

        DB::commit();
        
        return response()->json([
            'status' => true,
            'level' => new LevelResource($level)
        ]);
    }
    
    public function update(Request $request)
    {
        DB::beginTransaction();

        $result = (new LevelService)->updateLevel(
            LevelDTO::new()->fromArray([
                'name' => $request->get('name'),
                'value' => $request->get('value'),
                'description' => $request->get('description'),
                'user' => $request->user(),
                'levelId' => $request->level_id
            ])
        );

        $level = Level::find($request->level_id);
        
        if ($request->user()->isAdmin()) {
            (new ActivityService)->createActivity(
                ActivityDTO::new()->fromArray([
                    'performedon' => $level,
                    'performedby' => $request->user(),
                    'action' => 'update'
                ])
            );
        }

        DB::commit();

        return response()->json([
            'status' => (bool) $result,
            'level' => new LevelResource($level)
        ]);
    }
    
    public function delete(Request $request)
    {
        DB::beginTransaction();

        $level = Level::find($request->level_id);

        if ($request->user()->isAdmin()) {
            (new ActivityService)->createActivity(
                ActivityDTO::new()->fromArray([
                    'performedon' => $level,
                    'performedby' => $request->user(),
                    'action' => 'delete'
                ])
            );
        }

        (new LevelService)->deleteLevel(
            LevelDTO::new()->fromArray([
                'user' => $request->user(),
                'level' => $level
            ])
        );

        DB::commit();

        return response()->json([
            'status' => true,
        ]);
    }

    public function getLevel(Request $request)
    {
        $level = (new LevelService)->getLevel(
            LevelDTO::new()->fromArray([
                'levelId' => $request->query('id'),
                'name' => $request->query('name'),
            ])
        );

        return response()->json([
            'status' => true,
            'level' => $level ?
                new LevelResource($level) : 
                $level
        ]);
    }

    public function getLevels(Request $request)
    {
        $levels = (new LevelService)->getLevels(
            LevelDTO::new()->fromArray([
                'name' => $request->query('name'),
            ])
        );

        return LevelResource::collection($levels)->response()->getData(true);
    }
}
