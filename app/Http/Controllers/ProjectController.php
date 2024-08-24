<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Http\Request;
use App\Validation\Rules;
use App\Validation\OwnerCheck;
use App\Validation\Exists;

class ProjectController extends Controller
{
    use Rules, OwnerCheck, Exists;

    public function index(Request $request)
    {
        return response()->json($request->user()->projects);       
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

        return response()->json($project, 201);
    }

    public function show($project)
    {
        $project = $this->projectExist(Project::find($project));

        return response()->json($project->load('comments', 'tasks'));
    }

    public function update(Request $request,$project)
    {
        $userId = $request->user()->id;
        $project = $this->projectExist(Project::find($project));
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

        return response()->json($project);
        
    }

    public function destroy(Request $request,$project)
    {
        $userId = $request->user()->id;
        $project = $this->projectExist(Project::find($project));
        $this->checkOwnerProjectUser($project, $userId);
        
        $project->delete();

        return response()->noContent();
    }
}
