<?php

namespace App\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Validation\Rules;
use App\Validation\OwnerCheck;
use App\Validation\Exists;

/**
 * @group Tasks
 *
 * The Tasks group includes endpoints designed for task management.
 * It provides comprehensive functionality for handling tasks, covering all aspects from creation and viewing to updating and removal.
 * This group offers a thorough approach to managing and maintaining tasks associated with the user.
 */

class TaskController extends Controller
{

    use Rules, OwnerCheck, Exists;

    /**
     * List Tasks.
     * 
     * Retrieves a list of all tasks associated with the project.
     * 
     * @authenticated
     * 
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @response 200 {
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
     *       "updated_at": "2024-08-24T13:51:22.000000Z",
     *       "comments": [
     *               {
     *                 "id": 6,
     *                 "user_id": 78,
     *                 "task_id": 14,
     *                 "content": "comment content",
     *                 "created_at": "2024-08-24T15:58:57.000000Z",
     *                 "updated_at": "2024-08-24T15:58:57.000000Z"
     *               }
     *           ]
     *     }
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

    public function index(Request $request,$project)
    {
        $project = $this->projectExist(Project::find($project));

        return response()->json($project->tasks->load('comments'));    
    }

    /**
     * Assigned Tasks.
     * 
     * Retrieves a list of tasks assigned to the user, allowing them to view tasks under their responsibility.
     * 
     * @authenticated
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @response 200 {
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
     *       "updated_at": "2024-08-24T13:51:22.000000Z",
     *     }
     * }
     *
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     *
    */

    public function assignedTasks(Request $request)
    {
        return response()->json($request->user()->assignedTasks);
    }


    /**
     * 
     * Create Task.
     * 
     * Creates a new task based on the provided information, adding it to the user's task set.
     * 
     * @authenticated
     *
     * @bodyParam title string required The title of the project, must be at least 10 characters and a maximum of 150 characters.
     * @bodyParam description string required The description of the project, must be a minimum of 25 characters and a maximum of 1,500 characters.
     * @bodyParam start_at string required The start date of the project in Y-m-d (2024-12-31) and must be a date after or equal to today.
     * @bodyParam end_at string required The end date of the project in Y-m-d (2025-01-10) and must be a date after start at.
     * @bodyParam status string required The status of the project. Enum: 'available-soon','in-progress', 'done'
     * @bodyParam priority string required The priority of the project. Enum: 'low', 'medium', 'high'
     * @bodyParam assigned_email string required The assigned_email of the project and there must be a user with that email
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @response 201 {
     *   "title": "Task Title",
     *   "description": "Task Description",
     *   "start_at": "2024-12-31",
     *   "end_at": "2025-01-10",
     *   "status": "available-soon",
     *   "priority": "low",
     *   "assigned_email": " testuser@email.com",
     *   "project_id": 1,
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
     *   "message": "The title field is required. (and 4 more errors)",
     *   "errors": {
     *     "title": ["The title field is required."],
     *     "description": ["The description field is required."],
     *     "start_at": ["The start_at field is required."],
     *     "end_at": ["The end_at field is required."],
     *     "status": ["The status field is required."],
     *     "priority": ["The priority field is required."],
     *     "assigned_email": ["The assigned_email field is required."]
     *   }
     * }
     * @response 422 {
     *   "message": "The title field must be at least 10 characters. (and 4 more errors)",
     *   "errors": {
     *     "title": ["The title field must be at least 10 characters."],
     *     "description": ["The description field must be at least 25 characters."],
     *     "start_at": ["The start at field must be a date after or equal to today."],
     *     "end_at": ["The end at field must be a date after start at."],
     *     "status": ["The selected status is invalid."],
     *     "priority": ["The selected priority is invalid."],
     *     "assigned_email": ["The selected assigned_email is invalid."]
     *   }
     * }
    */

    public function store(Request $request, $project)
    {
        $userId = $request->user()->id;
        $project = $this->projectExist(Project::find($project));
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

        return response()->json($task, 201);
    }

    /**
     * 
     * View Task.
     * 
     * Retrieves details of a specific task, allowing for a complete view of the selected task’s information.
     * 
     * @authenticated
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @response 200 {
     *    "id": 1,
     *    "user_id": 1,
     *    "project_id": 1,
     *    "title": "Project Title",
     *    "description": "Project Description",
     *    "start_at": "2024-12-20",
     *    "end_at": "2025-01-20",
     *    "priority": "low",
     *    "status": "available-soon",
     *    "assigned_email": "testusedsar@email.com",
     *    "created_at": "2024-08-24T13:51:22.000000Z",
     *    "updated_at": "2024-08-24T13:51:22.000000Z",
     *    "comments": [
     *            {
     *              "id": 6,
     *              "user_id": 78,
     *              "task_id": 14,
     *              "content": "comment content",
     *              "created_at": "2024-08-24T15:58:57.000000Z",
     *              "updated_at": "2024-08-24T15:58:57.000000Z"
     *            }
     *        ]
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
     * @response 404 scenario="Task not found"{
     *   "message": "This Task does not exist"
     * }
    */

    public function show($project, $task)
    {
        $project = $this->projectExist(Project::find($project));
        $task = $this->taskExist(Task::find($task));
        $this->checkOwnerProjectTask($project, $task);

        return response()->json($task->load('comments'));
    }

    /**
     * 
     * Update Task.
     * 
     * Updates the information of an existing task based on the provided modifications.
     * 
     * @authenticated
     *
     * @bodyParam title string The title of the project, must be at least 10 characters and a maximum of 150 characters.
     * @bodyParam description string The description of the project, must be a minimum of 25 characters and a maximum of 1,500 characters.
     * @bodyParam start_at string The start date of the project in Y-m-d (2024-12-31) and must be a date after or equal to today.
     * @bodyParam end_at string The end date of the project in Y-m-d (2025-01-10) and must be a date after start at.
     * @bodyParam status string The status of the project. Enum: 'available-soon','in-progress', 'done'
     * @bodyParam priority string The priority of the project. Enum: 'low', 'medium', 'high'
     * @bodyParam assigned_email string The assigned_email of the project and there must be a user with that email
     *
     * @return \Illuminate\Http\JsonResponse
     *
     * @response 201 {
     *   "title": "Task Title",
     *   "description": "Task Description",
     *   "start_at": "2024-12-31",
     *   "end_at": "2025-01-10",
     *   "status": "available-soon",
     *   "priority": "low",
     *   "assigned_email": " testuser@email.com",
     *   "project_id": 1,
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
     * @response 404 scenario="Task not found"{
     *   "message": "This Task does not exist"
     * }
     *
     * @response 422 {
     *   "message": "The title field must be at least 10 characters. (and 4 more errors)",
     *   "errors": {
     *     "title": ["The title field must be at least 10 characters."],
     *     "description": ["The description field must be at least 25 characters."],
     *     "start_at": ["The start at field must be a date after or equal to today."],
     *     "end_at": ["The end at field must be a date after start at."],
     *     "status": ["The selected status is invalid."],
     *     "priority": ["The selected priority is invalid."],
     *     "assigned_email": ["The selected assigned_email is invalid."]
     *   }
     * }
    */

    public function update(Request $request, $project, $task)
    {
        $userId = $request->user()->id;
        $project = $this->projectExist(Project::find($project));
        $task = $this->taskExist(Task::find($task));
        $this->checkOwnerProjectUser($project, $userId);
        $this->checkOwnerProjectTask($project, $task);

        $data = $request->validate([
            'title' => $this->title(),
            'description' => $this->description(),
            'start_at' => $this->startDate(),
            'end_at' => $this->endDate(),
            'status' => $this->status(),
            'priority' => $this->priority(),
            'assigned_email' => $this->emailExists(),
        ]);

        $task->fill($data);
        $task->save();

        return response()->json($task);
    
    }

    /**
     * 
     * Delete Task.
     * 
     * Removes a specific task from the user's task set, permanently deleting it.
     * 
     * @authenticated
     *
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
     *   "message": "This project does not belong to you"
     * }
     *
     * @response 401 {
     *   "message": "This task does not belong to you"
     * }
     *
     * @response 404 scenario="Project not found"{
     *   "message": "This project does not exist"
     * }
     *
     * @response 404 scenario="Task not found"{
     *   "message": "This task does not exist"
     * }
     *
    */

    public function destroy(Request $request, $project, $task)
    {
        $userId = $request->user()->id;
        $project = $this->projectExist(Project::find($project));
        $task = $this->taskExist(Task::find($task));
        $this->checkOwnerProjectUser($project, $userId);
        $this->checkOwnerProjectTask($project, $task);

        $task->delete();

        return response()->noContent();
    }
}
