<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\Request;
use App\Validation\Rules;
use App\Validation\OwnerCheck;

class TaskCommentController extends Controller
{
    use Rules, OwnerCheck;

    public function index(Project $project, Task $task)
    {
        return $task->comments;
    }

    public function store(Request $request, Project $project, Task $task)
    {
        $userId = $request->user()->id;

        $data = $request->validate([
            'content' => $this->content(),
        ]);

        $foreign = [
            'task_id' => $task->id,
            'user_id' => $userId,
        ];

        $taskComment = TaskComment::create(array_merge($data, $foreign));

        return $taskComment;
    }

    public function show(TaskComment $comment)
    {
        return $comment;
    }

    public function destroy(Request $request, $comment)
    {
        $userId = $request->user()->id;
        $comment = TaskComment::find($comment);
        
        if(!$comment)
        {
            return response([
                'message' => 'This comment does not exist'
            ], 404);
        }

        $this->checkOwnerCommentUser($comment, $userId);

        $comment->delete();

        return response()->noContent();
    }
}
