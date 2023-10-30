<?php

namespace App\Http\Controllers;

use App\Actions\ApiErrorHandlingAction;
use App\DTOs\ProjectSessionDTO;
use App\Http\Requests\CreateProjectSessionRequest;
use App\Http\Requests\UpdateProjectSessionRequest;
use App\Http\Resources\ProjectSessionResource;
use App\Services\ProjectSessionService;
use Illuminate\Http\Request;

class ProjectSessionController extends Controller
{
    public function create(CreateProjectSessionRequest $request)
    {
        try {
            $projectSession = ProjectSessionService::new()->createProjectSession(
                ProjectSessionDTO::new()->fromArray([
                    "addedby" => $request->user(),
                    "projectId" => $request->project_id,
                    "name" => $request->name,
                    "description" => $request->description,
                    "dayOfWeek" => $request->day_of_week,
                    "startDate" => $request->start_date,
                    "endDate" => $request->end_date,
                    "startTime" => $request->start_time,
                    "endTime" => $request->end_time,
                    "type" => $request->type,
                    "period" => $request->period,
                ])
            );

            return response()->json([
                "status" => true,
                "projectSession" => new ProjectSessionResource($projectSession)
            ]);
        } catch (\Throwable $th) {
            // throw $th;
            return ApiErrorHandlingAction::make()
                ->execute($th);
        }
    }

    public function update(UpdateProjectSessionRequest $request)
    {
        try {
            $projectSession = ProjectSessionService::new()->updateProjectSession(
                ProjectSessionDTO::new()->fromArray([
                    "addedby" => $request->user(),
                    "name" => $request->name,
                    "description" => $request->description,
                    "dayOfWeek" => $request->day_of_week,
                    "startDate" => $request->start_date,
                    "endDate" => $request->end_date,
                    "startTime" => $request->start_time,
                    "endTime" => $request->end_time,
                    "type" => $request->type,
                    "period" => $request->period,
                    "projectSessionId" => $request->project_session_id,
                ])
            );

            return response()->json([
                "status" => true,
                "projectSession" => new ProjectSessionResource($projectSession)
            ]);
        } catch (\Throwable $th) {
            // throw $th;
            return ApiErrorHandlingAction::make()
                ->execute($th);
        }
    }

    public function delete(Request $request)
    {
        try {
            $status = ProjectSessionService::new()->deleteProjectSession(
                ProjectSessionDTO::new()->fromArray([
                    "addedby" => $request->user(),
                    "projectSessionId" => $request->project_session_id,
                ])
            );

            return response()->json([
                "status" => $status,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return ApiErrorHandlingAction::make()
                ->execute($th);
        }
    }
}
