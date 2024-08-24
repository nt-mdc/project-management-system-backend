<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Validation\Rules;
use App\Validation\OwnerCheck;
use App\Validation\Exists;

class TaskController extends Controller
{

    use Rules, OwnerCheck, Exists;

    public function index($project)
    {
        $project = $this->projectExist(Project::find($project));

        return response()->json($project->tasks->load('comments'));    
    }


    public function assignedTasks(Request $request)
    {
        return response()->json($request->user()->assignedTasks);
    }


    public function store(Request $request, $project)
    {
        $userId = $request->user()->id;
        $project = $this->projectExist(Project::find($project));
        $this->checkOwnerProjectUser($project, $userId);

        $data = $request->validate([
            'title' => $this->title(true),
            'description' => $this->description(true),
            'start_at' => $this->startDate(true),
            'end_at' => $this->endDate(true),
            'status' => $this->status(true),
            'priority' => $this->priority(true),
            'assigned_email' => $this->emailExists(true),
        ]);

        $foreign = [
            'project_id' => $project->id,
            'user_id' => $userId,
        ];

        $task = Task::create(array_merge($data, $foreign));

        return response()->json($task, 201);
    }

    public function show($project, $task)
    {
        $project = $this->projectExist(Project::find($project));
        $task = $this->taskExist(Task::find($task));
        $this->checkOwnerProjectTask($project, $task);

        return response()->json($task->load('comments'));
    }

    public function update(Request $request, $project, $task)
    {
        $userId = $request->user()->id;
        $project = $this->projectExist(Project::find($project));
        $task = $this->taskExist(Task::find($task));
        $this->checkOwnerProjectUser($project, $userId);
        $this->checkOwnerProjectTask($project, $task);

        $data = $request->validate([
            'title' => $this->title(),
            'description' => $this->description(),
            'start_at' => $this->startDate(),
            'end_at' => $this->endDate(),
            'status' => $this->status(),
            'priority' => $this->priority(),
            'assigned_email' => $this->emailExists(),
        ]);

        $task->fill($data);
        $task->save();

        return response()->json($task);
    
    }

    public function destroy(Request $request, $project, $task)
    {
        $userId = $request->user()->id;
        $project = $this->projectExist(Project::find($project));
        $task = $this->taskExist(Task::find($task));
        $this->checkOwnerProjectUser($project, $userId);
        $this->checkOwnerProjectTask($project, $task);

        $task->delete();

        return response()->noContent();
    }
}
