<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectComment;
use Illuminate\Http\Request;
use App\Validation\Rules;
use App\Validation\OwnerCheck;
use App\Validation\Exists;

/**
* @group Project Comments
*
* APIs for managing comments on projects.
*/

class ProjectCommentController extends Controller
{

    use Rules, OwnerCheck, Exists;

    /**
     * Shows all comments for a project
     * 
     * @authenticated
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @response 200 {
     *   {
     *     "id": 5,
     *     "user_id": 78,
     *     "project_id": 42,
     *     "content": "Comment content",
     *     "created_at": "2024-08-24T14:34:56.000000Z",
     *     "updated_at": "2024-08-24T14:34:56.000000Z",
     *     "user": {
     *         "id": 78,
     *         "name": "User test",
     *         "email": "testuser@email.com",
     *         "created_at": "2024-08-24T13:50:23.000000Z",
     *         "updated_at": "2024-08-24T13:50:23.000000Z"
     *     }
     *   }
     * }
     *
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     *
     * @response 404 {
     *   "message": "This project does not exist"
     * }
    */

    public function index($project)
    {
        $project = $this->projectExist(Project::find($project));

        return response()->json($project->comments->load('user'));
    }

    /**
     * Create a comment for a project
     * 
     * @authenticated
     *
     * @bodyParam content string required The content of the comment, must be a minimum of 5 characters and a maximum of 1,500 characters.
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @response 201 {
     *       "content": "Comment content",
     *       "project_id": 42,
     *       "user_id": 78,
     *       "updated_at": "2024-08-24T14:34:56.000000Z",
     *       "created_at": "2024-08-24T14:34:56.000000Z",
     *       "id": 5
     * }
     *
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     *
     * @response 404 scenario="Project not found"{
     *   "message": "This project does not exist"
     * }
     *
     * @response 422 scenario="Missing content field" {
     *   "message": "The content field is required.",
     *   "errors": {
     *       "content": [
     *           "The content field is required."
     *       ]
     *   }
     * }
     *
     * @response 422 scenario="Content field with fewer characters than the minimum" {
     *   "message": "The content field must be at least 5 characters.",
     *   "errors": {
     *       "content": [
     *           "The content field must be at least 5 characters."
     *       ]
     *   }
     * }
     *
     * @response 422 scenario="Content field with more characters than the maximum" {
     *   "message": "The content field must not be greater than 1500 characters.",
     *   "errors": {
     *       "content": [
     *           "The content field must not be greater than 1500 characters."
     *       ]
     *   }
     * }
    */

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

    /**
     * Show a specific comment in the project
     * 
     * @authenticated
     *
     * @urlParam project integer required The ID of the project. Example: 1
     * @urlParam comment integer required The ID of the comment. Example: 1
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @response 200 {
     *   "id": 5,
     *   "user_id": 78,
     *   "project_id": 42,
     *   "content": "Comment content",
     *   "created_at": "2024-08-24T14:34:56.000000Z",
     *   "updated_at": "2024-08-24T14:34:56.000000Z"
     * }
     *
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     *
     * @response 404 scenario="Project not found"{
     *   "message": "This project does not exist"
     * }
     *
     * @response 404 scenario="Comment not found"{
     *   "message": "This comment does not exist"
     * }}
    */

    public function show($project, $comment)
    {
        $project = $this->projectExist(Project::find($project));
        $comment = $this->commentExist(ProjectComment::find($comment));

        return response()->json($comment);
    }

    /**
     * Delete a comment
     * 
     * @authenticated
     *
     * @urlParam project integer required The ID of the project. Example: 1
     * @urlParam comment integer required The ID of the comment. Example: 1
     *
     * @return \Illuminate\Http\Response
     *
     * @response 204 scenario="No content"
     *
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     *
     * @response 401 {
     *   "message": "This comment does not belong to you"
     * }
     *
     * @response 404 scenario="Project not found"{
     *   "message": "This project does not exist"
     * }
     *
     * @response 404 scenario="Comment not found"{
     *   "message": "This comment does not exist"
     * }
    */

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
