<?php

namespace App\Http\Controllers;

use App\Actions\GetModelAction;
use App\DTOs\ProjectDTO;
use App\Http\Requests\CreateProjectRequest;
use App\Http\Requests\UpdateProjectRequest;
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

        $project = ProjectService::new()->removeParticipants(
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
    
    public function become(Request $request)
    {
        
    }
    
    public function leave(Request $request)
    {
        
    }
    
    public function getProject(Request $request)
    {
        
    }
    
    public function getProjects(Request $request)
    {
        
    }
    
    public function respondToRequest(Request $request)
    {
        
    }
}
