<?php

namespace App\Http\Controllers;

use App\Actions\GetModelAction;
use App\DTOs\ProjectDTO;
use App\Http\Requests\CreateProjectRequest;
use App\Http\Requests\GetProjectsRequest;
use App\Http\Requests\UpdateProjectRequest;
use App\Http\Resources\DetailedProjectResource;
use App\Http\Resources\ProjectParticipantResource;
use App\Http\Resources\ProjectResource;
use App\Services\ProjectService;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function create(CreateProjectRequest $request)
    {
        $user = $request->user();

        if ($request->has('for') && $user->isAdmin())
        {
            $user = GetModelAction::make()->execute(
                $request->for, $request->forId);
        }

        $project = ProjectService::new()->createProject(
            ProjectDTO::new()->fromArray([
                'name' => $request->name,
                'description' => $request->description,
                'addedby' => $user
            ])
        );

        return response()->json([
            'success' => true,
            'project' => new ProjectResource($project)
        ]);
    }
    
    public function update(UpdateProjectRequest $request)
    {
        $project = ProjectService::new()->updateProject(
            ProjectDTO::new()->fromArray([
                'name' => $request->name,
                'description' => $request->description,
                'addedby' => $request->user(),
                'projectId' => $request->project_id
            ])
        );

        return response()->json([
            'success' => true,
            'project' => new ProjectResource($project)
        ]);
    }
    
    public function delete(Request $request)
    {
        ProjectService::new()->deleteProject(
            ProjectDTO::new()->fromArray([
                'addedby' => $request->user(),
                'projectId' => $request->project_id
            ])
        );

        return response()->json([
            'success' => true,
        ]);
    }

    public function addSkills(Request $request)
    {
        $request->validate([
            'skillIds' => 'required|array'
        ]);

        $project = ProjectService::new()->addSkillsToProject(
            ProjectDTO::new()->fromArray([
                'projectId' => $request->project_id,
                'addedby' => $request->user()
            ]), $request->skillIds
        );

        return response()->json([
            'success' => true,
            'project' => new ProjectResource($project)
        ]);
    }

    public function removeSkills(Request $request)
    {
        $request->validate([
            'skillIds' => 'required|array'
        ]);

        $project = ProjectService::new()->removeSkillsFromProject(
            ProjectDTO::new()->fromArray([
                'projectId' => $request->project_id,
                'addedby' => $request->user()
            ]), $request->skillIds
        );

        return response()->json([
            'success' => true,
            'project' => new ProjectResource($project)
        ]);
    }

    public function sendParticipationInvitation(Request $request)
    {
        $request->validate([
            'participations' => 'required|array'
        ]);

        $project = ProjectService::new()->sendParticipationRequest(
            ProjectDTO::new()->fromArray([
                'projectId' => $request->project_id,
                'addedby' => $request->user(),
                'participations' => $request->participations
            ])
        );

        return response()->json([
            'success' => true
        ]);
    }

    public function removeParticipants(Request $request)
    {
        $request->validate([
            'participations' => 'required|array'
        ]);

        ProjectService::new()->removeParticipants(
            ProjectDTO::new()->fromArray([
                'projectId' => $request->project_id,
                'addedby' => $request->user(),
                'participations' => $request->participations
            ])
        );

        return response()->json([
            'success' => true
        ]);
    }
    
    public function leave(Request $request)
    {
        try {
            ProjectService::new()->leaveProject(
                ProjectDTO::new()->fromArray([
                    "addedby" => $request->user(),
                    "projectId" => $request->project_id,
                    "participationType" => $request->participation_type
                ])
            );

            return response()->json([
                "status" => true
            ], 204);
        } catch (\Throwable $th) {
            //throw $th;

            return response()->json([
                "message" => $th->getMessage()
            ], $th->getCode() ?: 500);
        }
    }
    
    public function getProject(Request $request)
    {
        try {
            $project = ProjectService::new()->getProject(
                ProjectDTO::new()->fromArray([
                    "addedby" => $request->user(),
                    "projectId" => $request->project_id,
                ])
            );
            
            return response()->json([
                "status" => true,
                "project" => new DetailedProjectResource($project)
            ]);
        } catch (\Throwable $th) {
            // throw $th;
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
            ], $th->getCode() ?: 500);
        }
    }
    
    public function getProjects(GetProjectsRequest $request)
    {
        try {
            $projects = ProjectService::new()->getProjects(
                ProjectDTO::new()->fromArray([
                    "name" => $request->name,
                    "like" => $request->like,
                    "ownerId" => $request->owner_id,
                    "ownerType" => $request->owner_type,
                    "officialId" => $request->official_id,
                    "memberId" => $request->member_id,
                    "participantId" => $request->participant_id,
                    "participantType" => $request->participant_type,
                    "participationType" => $request->participation_type,
                    "page" => $request->page ?: null,
                    "skillName" => $request->skill_name,
                ])
            );
            
            return ProjectResource::collection($projects);
        } catch (\Throwable $th) {
            // throw $th;
            return response()->json([
                "message" => $th->getMessage(),
            ], $th->getCode() ?: 500);
        }
    }
    
    public function getParticipants(Request $request)
    {
        try {
            $participants = ProjectService::new()->getParticipants(
                ProjectDTO::new()->fromArray([
                    "addedby" => $request->user(),
                    "projectId" => $request->project_id,
                    "type" => $request->type,
                ])
            );
            
            return response()->json([
                "status" => true,
                "participants" => ProjectParticipantResource::collection($participants)
            ]);
        } catch (\Throwable $th) {
            // throw $th;
            return response()->json([
                "status" => false,
                "message" => $th->getMessage(),
            ], $th->getCode() ?: 500);
        }
    }
}
