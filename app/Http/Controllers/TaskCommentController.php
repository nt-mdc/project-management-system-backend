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

    public function index($project, $task)
    {
        $project = Project::find($project);
        $task = task::find($task);

        if(!$project)
        {
            return response([
                'message' => 'This project does not exist'
            ], 404);
        }

        if(!$task)
        {
            return response([
                'message' => 'This task does not exist'
            ], 404);
        }

        return $task->comments->load('user');
    }

    public function store(Request $request, $project, $task)
    {
        $userId = $request->user()->id;

        $project = Project::find($project);
        $task = task::find($task);

        if(!$project)
        {
            return response([
                'message' => 'This project does not exist'
            ], 404);
        }

        if(!$task)
        {
            return response([
                'message' => 'This task does not exist'
            ], 404);
        }

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

    public function show($project, $task, $comment)
    {
        $project = Project::find($project);
        $task = task::find($task);
        $comment = TaskComment::find($comment);

        if(!$project)
        {
            return response([
                'message' => 'This project does not exist'
            ], 404);
        }

        if(!$task)
        {
            return response([
                'message' => 'This task does not exist'
            ], 404);
        }

        if(!$comment)
        {
            return response([
                'message' => 'This comment does not exist'
            ], 404);
        }

        return $comment->load('user');
    }

    public function destroy(Request $request, $project, $task, $comment)
    {
        $userId = $request->user()->id;
        $comment = TaskComment::find($comment);
        $project = Project::find($project);
        $task = task::find($task);

        if(!$project)
        {
            return response([
                'message' => 'This project does not exist'
            ], 404);
        }

        if(!$task)
        {
            return response([
                'message' => 'This task does not exist'
            ], 404);
        }
        
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
