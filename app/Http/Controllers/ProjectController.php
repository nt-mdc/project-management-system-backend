<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use App\Validation\Rules;
use App\Validation\OwnerCheck;

class ProjectController extends Controller
{
    use Rules, OwnerCheck;

    public function index(Request $request)
    {
        return $request->user()->projects;        
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => $this->title(true),
            'description' => $this->description(true),
            'start_at' => $this->startDate(true),
            'end_at' => $this->endDate(true),
            'status' => $this->status(true),
        ]);

        $user = $request->user();

        $project = Project::create(array_merge($data, ['user_id' => $user->id]));

        return ['project' => $project];
    }

    public function show($project)
    {
        $project = Project::find($project);

        if(!$project)
        {
            return response([
                'message' => 'This project does not exist'
            ], 404);
        }

        return $project->load('comments', 'tasks');
    }

    public function update(Request $request,$project)
    {
        $userId = $request->user()->id;
        $project = Project::find($project);

        if(!$project)
        {
            return response([
                'message' => 'This project does not exist'
            ], 404);
        }

        $this->checkOwnerProjectUser($project, $userId);

        $data = $request->validate([
            'title' => $this->title(),
            'description' => $this->description(),
            'start_at' => $this->startDate(),
            'end_at' => $this->endDate(),
            'status' => $this->status(),
        ]);
        
        $project->fill($data);
        $project->save();

        return $project;
        
    }

    public function destroy(Request $request,$project)
    {
        $userId = $request->user()->id;
        $project = Project::find($project);

        if(!$project)
        {
            return response([
                'message' => 'This project does not exist'
            ], 404);
        }

        $this->checkOwnerProjectUser($project, $userId);
        
        $project->delete();

        return response()->noContent();
    }
}
