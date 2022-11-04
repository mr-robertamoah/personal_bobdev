<?php

namespace App\Services;

use App\DTOs\ActivityDTO;
use App\DTOs\JobDTO;
use App\Exceptions\JobException;
use App\Models\Job;
use App\Models\User;
use App\Models\UserType;

class JobService
{
    const AUTHORIZEDUSERTYPES = [
        UserType::ADMIN,
        UserType::SUPERADMIN,
        UserType::FACILITATOR
    ];

    public function createJob(JobDTO $jobDTO)
    {
        if (!$jobDTO->addedBy) {
            throw new JobException('Sorry! A valid user is required to perform this action.');
        }

        if ($this->doesntHaveAppropriateData($jobDTO)) {
            throw new JobException('Sorry! The name of the job is required.');
        }

        if ($this->isNotAuthorized($jobDTO)) {
           throw new JobException('Sorry! You are not authorized to create a job.');
        }

        return $jobDTO->addedBy->addedJobs()->create($jobDTO->getData());
    }

    public function updateJob(JobDTO $jobDTO)
    {
        if (!$jobDTO->addedBy) {
            throw new JobException('Sorry! A valid user is required to perform this action.');
        }

        if ($this->doesntHaveAppropriateData($jobDTO, 'update')) {
            throw new JobException('Sorry! A job id is required for this operation.');
        }
        
        $jobDTO = $jobDTO->withJob(Job::find($jobDTO->jobId));

        if (!$jobDTO->job) {
            throw new JobException("Sorry! The Job with id {$jobDTO->jobId} was not found.");
        }

        if ($this->isNotAuthorized($jobDTO, action: 'update')) {
           throw new JobException("Sorry! You are not authorized to update the job with name {$jobDTO->job->name}.");
        }

        $result = $jobDTO->job->update(
            $this->getData($jobDTO)
        );       
        
        return $result;
    }

    public function deleteJob(JobDTO $jobDTO)
    {
        if (!$jobDTO->addedBy) {
            throw new JobException('Sorry! A valid user is required to perform this action.');
        }

        if ($this->doesntHaveAppropriateData($jobDTO, 'delete')) {
            throw new JobException('Sorry! A job id is required for this operation.');
        }
        
        $jobDTO = $jobDTO->job ? $jobDTO : $jobDTO->withJob(Job::find($jobDTO->jobId));

        if (!$jobDTO->job) {
            throw new JobException("Sorry! The Job with id {$jobDTO->jobId} was not found.");
        }

        if ($this->isNotAuthorized($jobDTO, action: 'delete')) {
           throw new JobException("Sorry! You are not authorized to delete the job with name {$jobDTO->job->name}.");
        }

        if ($jobDTO->addedBy->isAdmin()) {
            $this->detachJobFromUsers($jobDTO);
        }

        if ($jobDTO->addedBy->isFacilitator()) {
            $this->detachJobFromUser($jobDTO);
        }
        
        if (!$jobDTO->addedBy->isAdmin() && $jobDTO->job->jobUsers()->count() > 0) {
            return 1;
        }
        
        $result = $jobDTO->job->delete();

        return $result;
    }

    public function getJob(JobDTO $jobDTO)
    {
        if ($jobDTO->jobId) {
            return Job::find($jobDTO->jobId);
        }
        
        if ($jobDTO->name) {
            return Job::where('name', $jobDTO->name)->first();
        }

        return null;
    }
    
    public function getJobs(JobDTO $jobDTO)
    {
        return Job::where('name', 'LIKE', "%{$jobDTO->name}%")->paginate(2);
    }

    public function attachJobToUser(JobDTO $jobDTO)
    {
        $jobDTO = $jobDTO->job ? $jobDTO : $jobDTO->withJob(Job::find($jobDTO->jobId));
        
        if ($this->doesntHaveAppropriateData($jobDTO, 'attach')) {
            throw new JobException('Sorry! You need a valid user and job to perform this action.');
        }

        if (!$jobDTO->attachedTo->isFacilitator()) {
            throw new JobException('Sorry! Only users that are facilitators can have job on this platform.');
        }

        if ($this->shouldNotAttach($jobDTO)) {
            throw new JobException('Sorry! You must be an admin if you are attaching job to a different account. The user you are trying to attach the job to must be a facilitator.');
        }

        return $jobDTO->attachedTo->jobUsers()->create(['job_id' => $jobDTO->job->id]);
    }

    public function detachJobFromUser(JobDTO $jobDTO)
    {
        $jobDTO = $jobDTO->job ? $jobDTO : $jobDTO->withJob(Job::find($jobDTO->jobId));
        
        $this->validateJob($jobDTO, "Sorry! There is no job to detach from this user.");
        
        $jobDTO->job->jobUserFromUserID($jobDTO->addedBy->id)?->delete();
    }

    public function detachJobFromUsers(JobDTO $jobDTO)
    {
        $this->validateJob($jobDTO, "Sorry! There is no job to detach from users.");

        $jobDTO->job->jobUsers()->delete();
    }

    private function validateJob(JobDTO $jobDTO, string $message)
    {
        $jobDTO = $jobDTO->job ? $jobDTO : $jobDTO->withJob(Job::find($jobDTO->jobId));

        if (!$jobDTO->job) {
            throw new JobException($message);
        }
    }

    private function shouldAttach(JobDTO $jobDTO)
    {
        if ($jobDTO->addedBy?->isAdmin() && $jobDTO->attachedTo?->isFacilitator()) {
            return true;
        }
        
        if ($jobDTO->addedBy?->isFacilitator() && $jobDTO->addedBy?->is($jobDTO->attachedTo)) {
            return true;
        }

        return false;
    }

    private function shouldNotAttach(JobDTO $jobDTO)
    {
        return !$this->shouldAttach($jobDTO);
    }

    private function isAuthorized(JobDTO $jobDTO, string $action)
    {
        if (in_array($action, ['create', 'update', 'delete'])) {
            return $jobDTO->addedBy->userTypes()->whereIn('name', self::AUTHORIZEDUSERTYPES)->exists();            
        }

        if (in_array($action, ['update', 'delete'])) {
            return $jobDTO->addedBy->id == $jobDTO->job->user_id || $jobDTO->addedBy->isAdmin();
        }

        return false;
    }

    private function isNotAuthorized(JobDTO $jobDTO, string $action = 'create')
    {
        return !$this->isAuthorized(
            jobDTO: $jobDTO, action: $action
        );
    }

    private function hasAppropriateData(JobDTO $jobDTO, string $action = 'create')
    {
        if ($action == 'create') {
            return !is_null($jobDTO->name) && strlen($jobDTO->name) > 0;
        }

        if ($action == 'attach') {
            return !is_null($jobDTO->attachedTo) && !is_null($jobDTO->job);
        }
        
        return !is_null($jobDTO->jobId);
    }

    private function doesntHaveAppropriateData(JobDTO $jobDTO, string $action = 'create')
    {
        return !$this->hasAppropriateData($jobDTO, $action);
    }

    private function getData(JobDTO $jobDTO) : array
    {
        $data = [];

        if ($jobDTO->name) {
            $data['name'] = $jobDTO->name;
        }

        if ($jobDTO->description) {
            $data['description'] = $jobDTO->description;
        }

        return $data;
    }
}