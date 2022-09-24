<?php

namespace App\Http\Controllers;

use App\DTOs\ActivityDTO;
use App\DTOs\SkillDTO;
use App\Http\Requests\CreateSkillRequest;
use App\Http\Resources\SkillResource;
use App\Models\Skill;
use App\Services\ActivityService;
use App\Services\SkillService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SkillController extends Controller
{
    public function create(CreateSkillRequest $request)
    {
        DB::beginTransaction();

        $skill = (new SkillService)->createSkill(
            SkillDTO::new()->fromArray([
                'name' => $request->get('name'),
                'description' => $request->get('description'),
                'user' => $request->user(),
                'skillTypeId' => $request->get('skill_type_id'),
            ])
        );

        if ($request->user()->isAdmin()) {
            (new ActivityService)->createActivity(
                ActivityDTO::new()->fromArray([
                    'performedon' => $skill,
                    'performedby' => $request->user(),
                    'action' => 'create'
                ])
            );
        }

        DB::commit();

        return response()->json([
            'status' => true,
            'skill' => new SkillResource($skill)
        ]);
    }
    
    public function update(Request $request)
    {
        DB::beginTransaction();

        $result = (new SkillService)->updateSkill(
            SkillDTO::new()->fromArray([
                'name' => $request->get('name'),
                'description' => $request->get('description'),
                'user' => $request->user(),
                'skillId' => $request->skill_id
            ])
        );

        $skill = Skill::find($request->skill_id);
        
        if ($request->user()->isAdmin()) {
            (new ActivityService)->createActivity(
                ActivityDTO::new()->fromArray([
                    'performedon' => $skill,
                    'performedby' => $request->user(),
                    'action' => 'update'
                ])
            );
        }

        DB::commit();

        return response()->json([
            'status' => (bool) $result,
            'skill' => new SkillResource($skill)
        ]);
    }
    
    public function delete(Request $request)
    {
        DB::beginTransaction();

        if ($request->user()->isAdmin()) {
            (new ActivityService)->createActivity(
                ActivityDTO::new()->fromArray([
                    'performedon' => Skill::find($request->skill_id),
                    'performedby' => $request->user(),
                    'action' => 'delete'
                ])
            );
        }

        (new SkillService)->deleteSkill(
            SkillDTO::new()->fromArray([
                'user' => $request->user(),
                'skillId' => $request->skill_id
            ])
        );

        DB::commit();

        return response()->json([
            'status' => true,
        ]);
    }

    public function getSkill(Request $request)
    {
        $skill = (new SkillService)->getSkill(
            SkillDTO::new()->fromArray([
                'skillId' => $request->query('id'),
                'name' => $request->query('name'),
            ])
        );

        return response()->json([
            'status' => true,
            'skill' => $skill ? new SkillResource($skill) : $skill
        ]);
    }

    public function getSkills(Request $request)
    {
        $skills = (new SkillService)->getSkills(
            SkillDTO::new()->fromArray([
                'name' => $request->query('name'),
            ])
        );

        return SkillResource::collection($skills)->response()->getData(true);
    }
}
