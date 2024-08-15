<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Http\Request;
use App\Validation\Rules;
use App\Validation\OwnerCheck;

class TaskCommentController extends Controller
{
    use Rules, OwnerCheck;

    public function index(Task $task)
    {
        return $task->comments;
    }

    public function store(Request $request, Task $task)
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

    public function show(TaskComment $taskComment)
    {
        //
    }

    public function destroy(TaskComment $taskComment)
    {
        //
    }
}
