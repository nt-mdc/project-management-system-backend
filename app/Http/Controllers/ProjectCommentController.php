<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectComment;
use Illuminate\Http\Request;
use App\Validation\Rules;
use App\Validation\OwnerCheck;

class ProjectCommentController extends Controller
{

    use Rules, OwnerCheck;

    public function index($project)
    {
        $project = Project::find($project);

        if(!$project)
        {
            return response([
                'message' => 'This project does not exist'
            ], 404);
        }

        return $project->comments->load('user');
    }

    public function store(Request $request, $project)
    {
        $userId = $request->user()->id;

        $project = Project::find($project);

        if(!$project)
        {
            return response([
                'message' => 'This project does not exist'
            ], 404);
        }

        $data = $request->validate([
            'content' => $this->content(),
        ]);

        $foreign = [
            'project_id' => $project->id,
            'user_id' => $userId,
        ];

        $projComment = ProjectComment::create(array_merge($data, $foreign));

        return $projComment;
    }

    public function show($project, $comment)
    {
        $project = Project::find($project);

        if(!$project)
        {
            return response([
                'message' => 'This project does not exist'
            ], 404);
        }

        $comment = ProjectComment::find($comment);

        if(!$comment)
        {
            return response([
                'message' => 'This comment does not exist'
            ], 404);
        }
        return $comment;
    }

    public function destroy(Request $request, $project, $comment)
    {
        $userId = $request->user()->id;
        $comment = ProjectComment::find($comment);
        $project = Project::find($project);
        if(!$project)
        {
            return response([
                'message' => 'This project does not exist'
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
