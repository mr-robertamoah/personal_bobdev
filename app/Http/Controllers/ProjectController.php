<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateProjectRequest;
use App\Models\Project;
use Illuminate\Http\Request;

class ProjectController extends Controller
{
    public function create(CreateProjectRequest $request)
    {
        
    }
    
    public function update(Request $request)
    {
        
    }
    
    public function delete(Project $project)
    {
        
    }

    public function addParticipant(Request $request, Project $project)
    {
        
    }

    public function removeParticipant(Request $request, Project $project)
    {
        
    }
    
    public function sendRequest(Project $project)
    {
        
    }
    
    public function respondToRequest(Project $project)
    {
        
    }
}
