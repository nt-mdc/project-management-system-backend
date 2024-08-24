<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\Request;
use App\Validation\Rules;
use App\Validation\OwnerCheck;
use App\Validation\Exists;

class TaskCommentController extends Controller
{
    use Rules, OwnerCheck, Exists;

    public function index($project, $task)
    {
        $project = $this->projectExist(Project::find($project));
        $task = $this->taskExist(Task::find($task));

        return response()->json($task->comments->load('user'));
    }

    public function store(Request $request, $project, $task)
    {
        $userId = $request->user()->id;
        $project = $this->projectExist(Project::find($project));
        $task = $this->taskExist(Task::find($task));

        $data = $request->validate([
            'content' => $this->content(),
        ]);

        $foreign = [
            'task_id' => $task->id,
            'user_id' => $userId,
        ];

        $taskComment = TaskComment::create(array_merge($data, $foreign));

        return response()->json($taskComment, 201);
    }

    public function show($project, $task, $comment)
    {
        $project = $this->projectExist(Project::find($project));
        $task = $this->taskExist(Task::find($task));
        $comment = $this->commentExist(TaskComment::find($comment));

        return response()->json($comment->load('user'));
    }

    public function destroy(Request $request, $project, $task, $comment)
    {
        $userId = $request->user()->id;
        $project = $this->projectExist(Project::find($project));
        $task = $this->taskExist(Task::find($task));
        $comment = $this->commentExist(TaskComment::find($comment));


        $this->checkOwnerCommentUser($comment, $userId);

        $comment->delete();

        return response()->noContent();
    }
}
