<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use App\Models\TaskComment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class TaskCommentControllerTest extends TestCase
{
    use RefreshDatabase;

    protected $token, $userId, $userEmail;

    protected function setUp(): void
    {
        parent::setUp();

        $user = User::factory()->create([
            'password' => Hash::make('@123admJ'),
        ]);

        $response = $this->post('/api/auth/login', [
            'email' => $user->email,
            'password' => '@123admJ',
        ]);

        $this->token = $response->json('token.access_token');
        $this->userId = $response->json('user.id');
        $this->userEmail = $response->json('user.email');
    }

    public function testIndexTaskComment_withValidInformation_returnsSuccessResponse()
    {
        $project = Project::factory()->create(['user_id' => $this->userId]);
        $task = Task::factory()->create(['user_id' => $this->userId, 'project_id' => $project->id, 'assigned_email' => $this->userEmail]);
        $taskComment = TaskComment::factory()->create(['user_id' => $this->userId, 'task_id' => $task->id]);

        $response = $this->withToken($this->token)->json('GET', '/api/v1/projects/'.$project->id.'/tasks/'.$task->id.'/comments');

        $response->assertStatus(200)
        ->assertJsonStructure([[
            "id",
            "user_id",
            "task_id",
            "content",
            "created_at",
            "updated_at",
            "user" => [
                'id',
                'name',
                'email',
                "created_at",
                "updated_at",
            ]
        ]]);
    }

    public function testIndexTaskComment_withInvalidProjectInformation_returnsBadResponse()
    {        
        $response = $this->withToken($this->token)->json('GET', '/api/v1/projects/10/tasks/10/comments');

        $response->assertStatus(404)
        ->assertJson([
            "message" => "This project does not exist",
        ]);
    }

    public function testIndexTaskComment_withInvalidTaskInformation_returnsBadResponse()
    {     
        $project = Project::factory()->create(['user_id' => $this->userId]);
        $response = $this->withToken($this->token)->json('GET', '/api/v1/projects/'.$project->id.'/tasks/10/comments');

        $response->assertStatus(404)
        ->assertJson([
            "message" => "This task does not exist",
        ]);
    }

    public function testStoreTaskComment_withValidInformation_returnsSuccessResponse()
    {
        $project = Project::factory()->create(['user_id' => $this->userId]);
        $task = Task::factory()->create(['user_id' => $this->userId, 'project_id' => $project->id, 'assigned_email' => $this->userEmail]);
        $taskComment = TaskComment::factory()->create(['user_id' => $this->userId, 'task_id' => $task->id]);

        $response = $this->withToken($this->token)->json('POST', '/api/v1/projects/'.$project->id.'/tasks/'.$task->id.'/comments', [
            'content' => Str::random(30)
        ]);

        $response->assertStatus(201)
        ->assertJsonStructure([
            "id",
            "user_id",
            "task_id",
            "content",
        ]);
    }

   public function testStoreTaskComment_withInvalidProjectInformation_returnsBadResponse()
   {        
        $response = $this->withToken($this->token)->json('POST', '/api/v1/projects/10/tasks/10/comments', [
            'content' => Str::random(30)
        ]);

            $response->assertStatus(404)
            ->assertJson([
                "message" => "This project does not exist",
            ]);
   }

   public function testStoreTaskComment_withInvalidTaskInformation_returnsBadResponse()
   {        
        $project = Project::factory()->create(['user_id' => $this->userId]);
        $response = $this->withToken($this->token)->json('POST', '/api/v1/projects/'.$project->id.'/tasks/10/comments', [
            'content' => Str::random(30)
        ]);

            $response->assertStatus(404)
            ->assertJson([
                "message" => "This task does not exist",
            ]);
   }

   public function testStoreTaskComment_withMissingContent_returnsBadResponse()
   {
        $project = Project::factory()->create(['user_id' => $this->userId]);
        $task = Task::factory()->create(['user_id' => $this->userId, 'project_id' => $project->id, 'assigned_email' => $this->userEmail]);
        
        $response = $this->withToken($this->token)->json('POST', '/api/v1/projects/'.$project->id.'/tasks/'.$task->id.'/comments');

        $response->assertStatus(422)
        ->assertJson([
            "message" => "The content field is required.",
            "errors" => [
                "content" => [
                    "The content field is required."
                ]
            ],
        ]);
   }

   public function testGetOneTaskComment_withValidInformation_returnsSuccessResponse()
   {
    $project = Project::factory()->create(['user_id' => $this->userId]);
    $task = Task::factory()->create(['user_id' => $this->userId, 'project_id' => $project->id, 'assigned_email' => $this->userEmail]);
    $taskComment = TaskComment::factory()->create(['user_id' => $this->userId, 'task_id' => $task->id]);

    $response = $this->withToken($this->token)->json('GET', '/api/v1/projects/'.$project->id.'/tasks/'.$task->id.'/comments/'.$taskComment->id);

        $response->assertStatus(200)
        ->assertJsonStructure([
            "id",
            "user_id",
            "task_id",
            "content",
            "created_at",
            "updated_at",
            "user" => [
                'id',
                'name',
                'email',
                "created_at",
                "updated_at",
            ]
        ]);
   }

   public function testGetOneTaskComment_withInvalidProjectInformation_returnsBadResponse()
   {        
        $response = $this->withToken($this->token)->json('GET', '/api/v1/projects/10/tasks/10/comments/1');

        $response->assertStatus(404)
        ->assertJson([
            "message" => "This project does not exist",
        ]);
   }
   
   public function testGetOneTaskComment_withInvalidTaskInformation_returnsBadResponse()
   {        
        $project = Project::factory()->create(['user_id' => $this->userId]);

        $response = $this->withToken($this->token)->json('GET', '/api/v1/projects/'.$project->id.'/tasks/10/comments/1');

        $response->assertStatus(404)
        ->assertJson([
            "message" => "This task does not exist",
        ]);
   }

   public function testGetOneTaskComment_withInvalidCommentInformation_returnsBadResponse()
   {
        $project = Project::factory()->create(['user_id' => $this->userId]);
        $task = Task::factory()->create(['user_id' => $this->userId, 'project_id' => $project->id, 'assigned_email' => $this->userEmail]);

        $response = $this->withToken($this->token)->json('GET', '/api/v1/projects/'.$project->id.'/tasks/'.$task->id.'/comments/1');

            $response->assertStatus(404)
            ->assertJson([
                "message" => "This comment does not exist",
            ]);
   }

   public function testDeleteTaskComment_withValidInformation_returnsSuccessResponse()
   {
        $project = Project::factory()->create(['user_id' => $this->userId]);
        $task = Task::factory()->create(['user_id' => $this->userId, 'project_id' => $project->id, 'assigned_email' => $this->userEmail]);
        $taskComment = TaskComment::factory()->create(['user_id' => $this->userId, 'task_id' => $task->id]);

        $response = $this->withToken($this->token)->json('DELETE', '/api/v1/projects/'.$project->id.'/tasks/'.$task->id.'/comments/'.$taskComment->id);

        $response->assertStatus(204);
   }

   public function testDeleteTaskComment_withInvalidProjectInformation_returnsBadResponse()
   {        
        $response = $this->withToken($this->token)->json('DELETE', '/api/v1/projects/10/tasks/10/comments/1');

        $response->assertStatus(404)
        ->assertJson([
            "message" => "This project does not exist",
        ]);
   }

   public function testDeleteTaskComment_withInvalidTaskInformation_returnsBadResponse()
   {        
        $project = Project::factory()->create(['user_id' => $this->userId]);

        $response = $this->withToken($this->token)->json('DELETE', '/api/v1/projects/'.$project->id.'/tasks/10/comments/1');

        $response->assertStatus(404)
        ->assertJson([
            "message" => "This task does not exist",
        ]);
   }

   public function testDeleteTaskComment_withInvalidCommentInformation_returnsBadResponse()
   {

        $project = Project::factory()->create(['user_id' => $this->userId]);
        $task = Task::factory()->create(['user_id' => $this->userId, 'project_id' => $project->id, 'assigned_email' => $this->userEmail]);

        $response = $this->withToken($this->token)->json('DELETE', '/api/v1/projects/'.$project->id.'/tasks/'.$task->id.'/comments/1');

        $response->assertStatus(404)
        ->assertJson([
            "message" => "This comment does not exist",
        ]);
   }

}
