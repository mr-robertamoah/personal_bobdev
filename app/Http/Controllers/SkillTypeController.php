<?php

namespace App\Http\Controllers;

use App\DTOs\ActivityDTO;
use App\DTOs\SkillTypeDTO;
use App\Http\Requests\CreateSkillTypeRequest;
use App\Http\Resources\SkillTypeResource;
use App\Models\SkillType;
use App\Services\ActivityService;
use App\Services\SkillTypeService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SkillTypeController extends Controller
{
    public function create(CreateSkillTypeRequest $request)
    {
        DB::beginTransaction();

        $skillType = (new SkillTypeService)->createSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => $request->get('name'),
                'description' => $request->get('description'),
                'addedBy' => $request->user()
            ])
        );

        if ($request->user()->isAdmin()) {
            (new ActivityService)->createActivity(
                ActivityDTO::new()->fromArray([
                    'performedon' => $skillType,
                    'performedby' => $request->user(),
                    'action' => 'create'
                ])
            );
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'skillType' => new SkillTypeResource($skillType)
        ]);
    }
    
    public function update(Request $request)
    {
        DB::beginTransaction();

        $result = (new SkillTypeService)->updateSkillType(
            SkillTypeDTO::new()->fromArray([
                'name' => $request->get('name'),
                'description' => $request->get('description'),
                'addedBy' => $request->user(),
                'skillTypeId' => $request->skill_type_id
            ])
        );

        $skillType = SkillType::find($request->skill_type_id);
        
        if ($request->user()->isAdmin()) {
            (new ActivityService)->createActivity(
                ActivityDTO::new()->fromArray([
                    'performedon' => $skillType,
                    'performedby' => $request->user(),
                    'action' => 'update'
                ])
            );
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'skillType' => new SkillTypeResource($skillType)
        ]);
    }
    
    public function delete(Request $request)
    {
        DB::beginTransaction();

        if ($request->user()->isAdmin()) {
            (new ActivityService)->createActivity(
                ActivityDTO::new()->fromArray([
                    'performedon' => SkillType::find($request->skill_type_id),
                    'performedby' => $request->user(),
                    'action' => 'delete'
                ])
            );
        }

        (new SkillTypeService)->deleteSkillType(
            SkillTypeDTO::new()->fromArray([
                'addedBy' => $request->user(),
                'skillTypeId' => $request->skill_type_id
            ])
        );

        DB::commit();

        return response()->json([
            'status' => true,
        ]);
    }

    public function getSkillType(Request $request)
    {
        $skillType = (new SkillTypeService)->getSkillType(
            SkillTypeDTO::new()->fromArray([
                'skillTypeId' => $request->query('id'),
                'name' => $request->query('name'),
            ])
        );

        return response()->json([
            'status' => true,
            'skillType' => $skillType ? new SkillTypeResource($skillType) : $skillType
        ]);
    }

    public function getSkillTypes(Request $request)
    {
        $skillTypes = (new SkillTypeService)->getSkillTypes(
            SkillTypeDTO::new()->fromArray([
                'name' => $request->query('name'),
            ])
        );

        return SkillTypeResource::collection($skillTypes)->response()->getData(true);
    }
}
