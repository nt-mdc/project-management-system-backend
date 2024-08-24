<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectComment;
use Illuminate\Http\Request;
use App\Validation\Rules;
use App\Validation\OwnerCheck;
use App\Validation\Exists;

class ProjectCommentController extends Controller
{

    use Rules, OwnerCheck, Exists;

    public function index($project)
    {
        $project = $this->projectExist(Project::find($project));

        return response()->json($project->comments->load('user'));
    }

    public function store(Request $request, $project)
    {
        $userId = $request->user()->id;
        $project = $this->projectExist(Project::find($project));

        $data = $request->validate([
            'content' => $this->content(),
        ]);

        $foreign = [
            'project_id' => $project->id,
            'user_id' => $userId,
        ];

        $projComment = ProjectComment::create(array_merge($data, $foreign));

        return response()->json($projComment, 201);
    }

    public function show($project, $comment)
    {
        $project = $this->projectExist(Project::find($project));
        $comment = $this->commentExist(ProjectComment::find($comment));

        return response()->json($comment);
    }

    public function destroy(Request $request, $project, $comment)
    {
        $userId = $request->user()->id;
        $project = $this->projectExist(Project::find($project));
        $comment = $this->commentExist(ProjectComment::find($comment));
        $this->checkOwnerCommentUser($comment, $userId);

        $comment->delete();

        return response()->noContent();
    }
}
