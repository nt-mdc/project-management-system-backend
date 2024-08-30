<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use App\Validation\Rules;
use App\Validation\OwnerCheck;
use App\Validation\Exists;

/**
* @group Projects
*
* APIs for managing projects.
*/

class ProjectController extends Controller
{
    use Rules, OwnerCheck, Exists;

    /**
     * List Projects.
     * 
     * Lists projects that the user has created or projects to which they have a task assigned.
     * 
      * @authenticated
      *
      * @return \Illuminate\Http\JsonResponse
      *
      * @response 200 {
      *   {
      *     "id": 1,
      *     "title": "Project Title",
      *     "description": "Project Description",
      *     "start_at": "2024-08-24T00:00:00.000000Z",
      *     "end_at": "2024-08-25T00:00:00.000000Z",
      *     "status": "available-soon",
      *     "user_id": 1,
      *     "updated_at": "2024-08-24T00:00:00.000000Z",
      *     "created_at": "2024-08-24T00:00:00.000000Z",
      *   }
      * }
      *
      * @response 401 {
      *   "message": "Unauthenticated."
      * }
    */

    public function index(Request $request)
    {
        $user = $request->user();
        $ProjectIdsByAssignedTasks = Task::where("assigned_email", $user->email)->select("project_id")->distinct()->pluck("project_id");
        $ProjectByAssignedTasks = Project::whereIn("id", $ProjectIdsByAssignedTasks)->get();
        $ProjectsCreatesByUser = $user->projects;
        $AllProjects = $ProjectByAssignedTasks->merge($ProjectsCreatesByUser)->unique('id')->sortBy('id')->values()->all();
        return response()->json($AllProjects);       
    }


    /**
     * Create Project.
     * 
     * @authenticated
     *
     * @bodyParam title string required The title of the project, must be at least 10 characters and a maximum of 150 characters.
     * @bodyParam description string required The description of the project, must be a minimum of 25 characters and a maximum of 1,500 characters.
     * @bodyParam start_at string required The start date of the project in Y-m-d (2024-12-31) and must be a date after or equal to today.
     * @bodyParam end_at string required The end date of the project in Y-m-d (2025-01-10) and must be a date after start at.
     * @bodyParam status string required The status of the project. Enum: 'available-soon','in-progress', 'done'
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @response 201 {
     *   "title": "Project Title",
     *   "description": "Project Description",
     *   "start_at": "2024-12-31",
     *   "end_at": "2025-01-10",
     *   "status": "available-soon",
     *   "user_id": 1,
     *   "updated_at": "2024-08-24T00:00:00.000000Z",
     *   "created_at": "2024-08-24T00:00:00.000000Z",
     *   "id": 1
     * }
     *
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     *
     * @response 422 {
     *   "message": "The title field is required. (and 4 more errors)",
     *   "errors": {
     *     "title": ["The title field is required."],
     *     "description": ["The description field is required."],
     *     "start_at": ["The start_at field is required."],
     *     "end_at": ["The end_at field is required."],
     *     "status": ["The status field is required."]
     *   }
     * }
     * @response 422 {
     *   "message": "The title field must be at least 10 characters. (and 4 more errors)",
     *   "errors": {
     *     "title": ["The title field must be at least 10 characters."],
     *     "description": ["The description field must be at least 25 characters."],
     *     "start_at": ["The start at field must be a date after or equal to today."],
     *     "end_at": ["The end at field must be a date after start at."],
     *     "status": ["The selected status is invalid."]
     *   }
     * }
    */

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => $this->title(true),
            'description' => $this->description(true),
            'start_at' => $this->startDate(true),
            'end_at' => $this->endDate(true),
            'status' => $this->status(true),
        ]);

        $user = $request->user();

        $project = Project::create(array_merge($data, ['user_id' => $user->id]));

        return response()->json($project, 201);
    }

    /**
     * View Project.
     * 
     * View a project with its tasks and comments.
     * 
     * @authenticated
     *
     * @urlParam project integer required The ID of the project. Example: 1
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @response 200 {
     *   "id": 1,
     *   "title": "Project Title",
     *   "description": "Project Description",
     *   "start_at": "2024-12-31",
     *   "end_at": "2025-01-10",
     *   "status": "available-soon",
     *   "user_id": 1,
     *   "created_at": "2024-08-24T00:00:00.000000Z",
     *   "updated_at": "2024-08-24T00:00:00.000000Z",
     *   "comments": [
     *     {
     *       "id": 1,
     *       "project_id": 1,
     *       "user_id": 1
     *       "content": "Comment content",
     *       "created_at": "2024-08-24T00:00:00.000000Z",
     *       "updated_at": "2024-08-24T00:00:00.000000Z",
     *     }
     *   ],
     *   "tasks": [
     *     {
     *       "id": 1,
     *       "user_id": 1,
     *       "project_id": 1,
     *       "title": "Project Title",
     *       "description": "Project Description",
     *       "start_at": "2024-12-20",
     *       "end_at": "2025-01-20",
     *       "priority": "low",
     *       "status": "available-soon",
     *       "assigned_email": "testusedsar@email.com",
     *       "created_at": "2024-08-24T13:51:22.000000Z",
     *       "updated_at": "2024-08-24T13:51:22.000000Z"
     *     }
     *   ]
     * }
     *
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     *
     * @response 404 scenario="Project not found"{
     *   "message": "This project does not exist"
     * }
    */

    public function show($project)
    {
        $project = $this->projectExist(Project::find($project));

        return response()->json($project->load('comments', 'tasks'));
    }


    /**
     * Update Project.
     * 
     * @authenticated
     * @urlParam project integer required The ID of the project. Example: 1
     * @bodyParam title string The title of the project, must be at least 10 characters and a maximum of 150 characters.
     * @bodyParam description string The description of the project, must be a minimum of 25 characters and a maximum of 1,500 characters.
     * @bodyParam start_at string The start date of the project in Y-m-d (2024-12-31) and must be a date after or equal to today.
     * @bodyParam end_at string The end date of the project in Y-m-d (2025-01-10) and must be a date after start at.
     * @bodyParam status string The status of the project. Enum: 'available-soon','in-progress', 'done'
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @response 200 {
     *   "title": "Project Title",
     *   "description": "Project Description",
     *   "start_at": "2024-12-31",
     *   "end_at": "2025-01-10",
     *   "status": "available-soon",
     *   "user_id": 1,
     *   "updated_at": "2024-08-24T00:00:00.000000Z",
     *   "created_at": "2024-08-24T00:00:00.000000Z",
     *   "id": 1
     * }
     *
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     *
     * @response 401 {
     *   "message": "This project does not belong to you"
     * }
     *
     * @response 404 scenario="Project not found"{
     *   "message": "This project does not exist"
     * }
     *
     * @response 422 {
     *   "message": "The title field must be at least 10 characters. (and 4 more errors)",
     *   "errors": {
     *     "title": ["The title field must be at least 10 characters."],
     *     "description": ["The description field must be at least 25 characters."],
     *     "start_at": ["The start at field must be a date after or equal to today."],
     *     "end_at": ["The end at field must be a date after start at."],
     *     "status": ["The selected status is invalid."]
     *   }
     * }
    */

    public function update(Request $request,$project)
    {
        $userId = $request->user()->id;
        $project = $this->projectExist(Project::find($project));
        $this->checkOwnerProjectUser($project, $userId);

        $data = $request->validate([
            'title' => $this->title(),
            'description' => $this->description(),
            'start_at' => $this->startDate(),
            'end_at' => $this->endDate(),
            'status' => $this->status(),
        ]);
        
        $project->fill($data);
        $project->save();

        return response()->json($project);
        
    }

    /**
     * Delete Project.
     * 
     * @authenticated
     *
     * @urlParam project integer required The ID of the project. Example: 1
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @response 204 scenario="No content"
     *
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     *
     * @response 401 {
     *   "message": "This project does not belong to you"
     * }
     *
     * @response 404 scenario="Project not found"{
     *   "message": "This project does not exist"
     * }
    */


    public function destroy(Request $request,$project)
    {
        $userId = $request->user()->id;
        $project = $this->projectExist(Project::find($project));
        $this->checkOwnerProjectUser($project, $userId);
        
        $project->delete();

        return response()->noContent();
    }
}
