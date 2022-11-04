<?php

namespace App\Http\Controllers;

use App\DTOs\ActivityDTO;
use App\DTOs\JobDTO;
use App\Http\Requests\CreateJobRequest;
use App\Http\Requests\UpdateJobRequest;
use App\Http\Resources\JobResource;
use App\Models\Job;
use App\Models\User;
use App\Services\ActivityService;
use App\Services\JobService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class JobController extends Controller
{
    public function create(CreateJobRequest $request)
    {
        // [user_id, name, description]
        $jobService = (new JobService);

        $job = $jobService->createJob(
            $jobDTO = JobDTO::new()->fromArray([
                'name' => $request->get('name'),
                'addedBy' => $request->user()
            ])
        );

        if ($request->get('attach')) {
            $jobService->attachJobToUser(
                $jobDTO->fromArray([
                    'job' => $job,
                    'attachedTo' => $request->get('user_id') ? 
                        User::find($request->get('user_id')) : 
                        $request->user()
                ])
            );
        }

        if ($request->user()->isAdmin()) {
            (new ActivityService)->createActivity(
                ActivityDTO::new()->fromArray([
                    'performedon' => $job,
                    'performedby' => $request->user(),
                    'action' => 'create'
                ])
            );
        }

        return response()->json([
            'status' => true,
            'job' => $job
        ]);
    }

    public function attach(Request $request)
    {
        // [job_id, ]

        (new JobService)->attachJobToUser(
            JobDTO::new()->fromArray([
                'jobId' => $request->route('job_id'),
                'attachedTo' => $request->get('user_id') ? 
                    User::find($request->get('user_id')) : 
                    $request->user(),
                'addedBy' => $request->user(),
            ])
        );

        return response()->json([
            'status' => true,
        ]);
    }

    public function detach(Request $request)
    {
        // [job_id, ]

        (new JobService)->detachJobFromUser(
            JobDTO::new()->fromArray([
                'jobId' => $request->route('job_id'),
                'addedBy' => $request->user()
            ])
        );

        return response()->json([
            'status' => true,
        ]);
    }
    
    public function update(UpdateJobRequest $request)
    {
        // [job_id,]
        $jobService = (new JobService);

        DB::beginTransaction();

        $result = $jobService->updateJob(
            JobDTO::new()->fromArray([
                'name' => $request->get('name'),
                'addedBy' => $request->user(),
                'jobId' => $request->job_id
            ])
        );

        if ($request->user()->isAdmin()) {
            (new ActivityService)->createActivity(
                ActivityDTO::new()->fromArray([
                    'performedon' => Job::find($request->job_id),
                    'performedby' => $request->user(),
                    'action' => 'update'
                ])
            );
        }

        DB::commit();

        return response()->json([
            'status' => $result
        ]);
    }
    
    public function delete(Request $request)
    {
        // [job_id,]
        $jobService = (new JobService);

        DB::beginTransaction();

        if ($request->user()->isAdmin()) {
            (new ActivityService)->createActivity(
                ActivityDTO::new()->fromArray([
                    'performedon' => Job::find($request->job_id),
                    'performedby' => $request->user(),
                    'action' => 'delete'
                ])
            );
        }

        $result = $jobService->deleteJob(
            JobDTO::new()->fromArray([
                'name' => $request->get('name'),
                'addedBy' => $request->user(),
                'jobId' => $request->job_id
            ])
        );

        DB::commit();

        return response()->json([
            'status' => $result
        ]);
    }

    public function getJob(Request $request)
    {
        $job = (new JobService)->getJob(
            JobDTO::new()->fromArray([
                'jobId' => $request->query('id'),
                'name' => $request->query('name'),
            ])
        );

        return response()->json([
            'status' => true,
            'job' => $job ? new JobResource($job) : $job
        ]);
    }

    public function getJobs(Request $request)
    {
        $jobs = (new JobService)->getJobs(
            JobDTO::new()->fromArray([
                'name' => $request->query('name'),
            ])
        );

        return JobResource::collection($jobs)->response()->getData(true);
    }
}
