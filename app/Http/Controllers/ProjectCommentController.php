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

    public function index(Project $project)
    {
        return $project->comments;
    }

    public function store(Request $request, Project $project)
    {

        $userId = $request->user()->id;

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

    public function show(ProjectComment $comment)
    {
        return $comment;
    }

    public function destroy(Request $request, $comment)
    {
        $userId = $request->user()->id;
        $comment = ProjectComment::find($comment);
        
        $this->checkOwnerCommentUser($comment, $userId);

        if(!$comment)
        {
            return response([
                'message' => 'This comment does not exist'
            ], 404);
        }

        $comment->delete();

        return response()->noContent();
    }
}
