<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Project;
use App\Models\ProjectComment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProjectCommentControllerTest extends TestCase
{

    use RefreshDatabase;

    protected $token, $userId;

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
    }

    public function testIndexProjectComment_withValidInformation_returnsSuccessResponse()
   {
        $project = Project::factory()->create(['user_id' => $this->userId]);
        
        $projectComment = ProjectComment::factory()->create(['user_id' => $this->userId, 'project_id' => $project->id]);

        $response = $this->withToken($this->token)->json('GET', '/api/v1/projects/'.$project->id.'/comments');

        $response->assertStatus(200)
        ->assertJsonStructure([[
            "id",
            "user_id",
            "project_id",
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

   public function testIndexProjectComment_withInvalidInformation_returnsBadResponse()
   {        
        $response = $this->withToken($this->token)->json('GET', '/api/v1/projects/10/comments');

        $response->assertStatus(404)
        ->assertJson([
            "message" => "This project does not exist",
        ]);
   }

   public function testStoreProjectComment_withValidInformation_returnsSuccessResponse()
   {
        $project = Project::factory()->create(['user_id' => $this->userId]);
        
        $response = $this->withToken($this->token)->json('POST', '/api/v1/projects/'.$project->id.'/comments', [
            'content' => Str::random(30)
        ]);

        $response->assertStatus(201)
        ->assertJsonStructure([
            "id",
            "user_id",
            "project_id",
            "content",
        ]);
   }

   public function testStoreProjectComment_withInvalidInformation_returnsBadResponse()
   {        
    $response = $this->withToken($this->token)->json('POST', '/api/v1/projects/10/comments', [
        'content' => Str::random(30)
    ]);

        $response->assertStatus(404)
        ->assertJson([
            "message" => "This project does not exist",
        ]);
   }

   public function testStoreProjectComment_withMissingContent_returnsBadResponse()
   {
        $project = Project::factory()->create(['user_id' => $this->userId]);
        
        $response = $this->withToken($this->token)->json('POST', '/api/v1/projects/'.$project->id.'/comments');

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

   public function testGetOneProjectComment_withValidInformation_returnsSuccessResponse()
   {
        $project = Project::factory()->create(['user_id' => $this->userId]);
        
        $projectComment = ProjectComment::factory()->create(['user_id' => $this->userId, 'project_id' => $project->id]);

        $response = $this->withToken($this->token)->json('GET', '/api/v1/projects/'.$project->id.'/comments/'.$projectComment->id);

        $response->assertStatus(200)
        ->assertJsonStructure([
            "id",
            "user_id",
            "project_id",
            "content",
            "created_at",
            "updated_at",
        ]);
   }

   public function testGetOneProjectComment_withInvalidProjectInformation_returnsBadResponse()
   {        
    $response = $this->withToken($this->token)->json('GET', '/api/v1/projects/10/comments/1');

        $response->assertStatus(404)
        ->assertJson([
            "message" => "This project does not exist",
        ]);
   }

   public function testGetOneProjectComment_withInvalidCommentInformation_returnsBadResponse()
   {

    $project = Project::factory()->create(['user_id' => $this->userId]);

    $response = $this->withToken($this->token)->json('GET', '/api/v1/projects/'.$project->id.'/comments/0');

        $response->assertStatus(404)
        ->assertJson([
            "message" => "This comment does not exist",
        ]);
   }

   public function testDeleteProjectComment_withValidInformation_returnsSuccessResponse()
   {
        $project = Project::factory()->create(['user_id' => $this->userId]);
        
        $projectComment = ProjectComment::factory()->create(['user_id' => $this->userId, 'project_id' => $project->id]);

        $response = $this->withToken($this->token)->json('DELETE', '/api/v1/projects/'.$project->id.'/comments/'.$projectComment->id);

        $response->assertStatus(204);
   }

   public function testDeleteProjectComment_withInvalidProjectInformation_returnsBadResponse()
   {        
    $response = $this->withToken($this->token)->json('DELETE', '/api/v1/projects/10/comments/1');

        $response->assertStatus(404)
        ->assertJson([
            "message" => "This project does not exist",
        ]);
   }

   public function testDeleteProjectComment_withInvalidCommentInformation_returnsBadResponse()
   {

    $project = Project::factory()->create(['user_id' => $this->userId]);

    $response = $this->withToken($this->token)->json('DELETE', '/api/v1/projects/'.$project->id.'/comments/10');

        $response->assertStatus(404)
        ->assertJson([
            "message" => "This comment does not exist",
        ]);
   }



   
}
