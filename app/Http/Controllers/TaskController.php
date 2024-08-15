<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Validation\Rules;
use App\Validation\OwnerCheck;

class TaskController extends Controller
{

    use Rules, OwnerCheck;

    public function index(Project $projects)
    {
        return $projects->tasks;    
    }


    public function assignedTasks(Request $request)
    {
        return $request->user()->assignedTasks;
    }


    public function store(Request $request, Project $project)
    {
        $userId = $request->user()->id;

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

        return $task;
    }

    /**
     * Display the specified resource.
     */
    public function show(Project $project, Task $task)
    {
        $this->checkOwnerProjectTask($project, $task);

        return $task;
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Project $project, Task $task)
    {
        $userId = $request->user()->id;

        $this->checkOwnerProjectUser($project, $userId);
        $this->checkOwnerProjectTask($project, $task);

        $data = $request->validate([
            'title' => $this->title(),
            'description' => $this->description(),
            'start_at' => $this->startDate(),
            'end_at' => $this->endDate(),
            'status' => $this->status(),
            'priority' => $this->priority(),
            'assigned_email' => $this->emailUnique(),
        ]);

        $task->fill($data);
        $task->save();

        return $task;    
    
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, Project $project,$task)
    {
        $userId = $request->user()->id;
        $task = task::find($task);

        $this->checkOwnerProjectUser($project, $userId);

        if(!$task)
        {
            return response([
                'message' => 'This task does not exist'
            ], 404);
        }

        $this->checkOwnerProjectTask($project, $task);

        $task->delete();

        return response()->noContent();
    }
}
