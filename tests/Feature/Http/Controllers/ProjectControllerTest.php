<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Project;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class ProjectControllerTest extends TestCase
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

    public function testIndexProject_withValidInformation_returnsSuccessResponse()
    {
        $project = Project::factory()->create(['user_id' => $this->userId]);

        $response = $this->withToken($this->token)->json('GET', '/api/v1/projects');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    [
                        'id',
                        'title',
                        'description',
                        'start_at',
                        'end_at',
                        'status',
                        'user_id',
                        'created_at',
                        'updated_at',
                    ],
                ]);
   }

    public function testStoreProject_withValidInformation_returnsSuccessResponse()
    {
        $data = [
            'title' => Str::random(10),
            'description' => Str::random(30),
            'start_at' => "2040-10-30",
            'end_at' =>  "2040-11-30",
            'status' => "available-soon"
        ];

        $response = $this->withToken($this->token)->json('POST', '/api/v1/projects', $data);

        $response->assertStatus(201)
                ->assertJsonStructure([
                    'title',
                    'description',
                    'start_at',
                    'end_at',
                    'status',
                    'user_id',
                    'updated_at',
                    'created_at',
                    'id'
                ]);
   }


   public function testStoreProject_withMissingTitle_returnsBadResponse()
    {
        $data = [
            'description' => Str::random(30),
            'start_at' => "2040-10-30",
            'end_at' =>  "2040-11-30",
            'status' => "available-soon"
        ];

        $response = $this->withToken($this->token)->json('POST', '/api/v1/projects', $data);

        $response->assertStatus(422)
                ->assertJson([
                    "message" => "The title field is required.",
                    "errors" => [
                        "title" => [
                            "The title field is required."
                        ]
                    ]
                ]);
    }


    public function testStoreProject_withMissingDescription_returnsBadResponse()
    {
        $data = [
            'title' => Str::random(10),
            'start_at' => "2040-10-30",
            'end_at' =>  "2040-11-30",
            'status' => "available-soon"
        ];

        $response = $this->withToken($this->token)->json('POST', '/api/v1/projects', $data);

        $response->assertStatus(422)
        ->assertJson([
            "message" => "The description field is required.",
            "errors" => [
                "description" => [
                    "The description field is required."
                ]
            ]
        ]);
    }

    public function testStoreProject_withMissingStart_returnsBadResponse()
    {
        $data = [
            'title' => Str::random(10),
            'description' => Str::random(30),
            'end_at' =>  "2040-11-30",
            'status' => "available-soon"
        ];

        $response = $this->withToken($this->token)->json('POST', '/api/v1/projects', $data);

        $response->assertStatus(422)
        ->assertJson([
            "message" => "The start at field is required.",
            "errors" => [
                "start_at" => [
                    "The start at field is required."
                ]
            ]
        ]);
    }


    public function testStoreProject_withMissingEnd_returnsBadResponse()
    {
        $data = [
            'title' => Str::random(10),
            'description' => Str::random(30),
            'start_at' => "2040-10-30",
            'status' => "available-soon"
        ];

        $response = $this->withToken($this->token)->json('POST', '/api/v1/projects', $data);

        $response->assertStatus(422)
        ->assertJson([
            "message" => "The end at field is required.",
            "errors" => [
                "end_at" => [
                    "The end at field is required."
                ]
            ]
        ]);
    }

    public function testStoreProject_withMissingStatus_returnsBadResponse()
    {
        $data = [
            'title' => Str::random(10),
            'description' => Str::random(30),
            'start_at' => "2040-10-30",
            'end_at' =>  "2040-11-30",
        ];

        $response = $this->withToken($this->token)->json('POST', '/api/v1/projects', $data);

        $response->assertStatus(422)
        ->assertJson([
            "message" => "The status field is required.",
            "errors" => [
                "status" => [
                    "The status field is required."
                ]
            ]
        ]);
    }

    public function testStoreProject_withMissingAllFields_returnsBadResponse()
    {

        $response = $this->withToken($this->token)->json('POST', '/api/v1/projects');

        $response->assertStatus(422)
        ->assertJson([
            "message" => "The title field is required. (and 4 more errors)",
            "errors" => [
                "title" => [
                    "The title field is required."
                ],
                "description" => [
                    "The description field is required."
                ],
                "start_at" => [
                    "The start at field is required."
                ],
                "end_at" => [
                    "The end at field is required."
                ],
                "status" => [
                    "The status field is required."
                ]
            ]
        ]);
    }

    public function testGetOneProject_withValidInformation_returnsSuccessResponse()
    {

        $project = Project::factory()->create(['user_id' => $this->userId]);

        $response = $this->withToken($this->token)->json('GET', '/api/v1/projects/'.$project->id);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'id',
                    'title',
                    'description',
                    'start_at',
                    'end_at',
                    'status',
                    'user_id',
                    'created_at',
                    'updated_at',
                    'comments',
                    'tasks'
                ]);
   }

   public function testGetOneProject_withInvalidInformation_returnsBadResponse()
    {

        $response = $this->withToken($this->token)->json('GET', '/api/v1/projects/80');

        $response->assertStatus(404)
                ->assertJson([
                    'message' => 'This project does not exist'
                ]);
   }

   public function testUpdateProject_withValidInformation_returnsSuccessResponse()
   {

    $project = Project::factory()->create(['user_id' => $this->userId]);

    $response = $this->withToken($this->token)->json('PUT', '/api/v1/projects/'.$project->id,[
        'title' => Str::random(12),
        'description' => Str::random(31),
        'status' => 'in-progress'
    ]);

    $response->assertStatus(200)
                ->assertJsonStructure([
                    'id',
                    'title',
                    'description',
                    'start_at',
                    'end_at',
                    'status',
                    'user_id',
                    'created_at',
                    'updated_at',
                ]);
   }

   public function testUpdateProject_withInvalidInformation_returnsBadResponse()
   {

    $project = Project::factory()->create(['user_id' => $this->userId]);

    $response = $this->withToken($this->token)->json('PUT', '/api/v1/projects/'.$project->id,[
        'title' => "as",
        'description' => "ad",
        'start_at' => "2000-10-30",
        'end_at' =>  "1999-11-30",
        'status' => "nothing"
    ]);

    $response->assertStatus(422)
                ->assertJson([
                    "message" => "The title field must be at least 10 characters. (and 4 more errors)",
                    "errors" => [
                        "title" => [
                            "The title field must be at least 10 characters."
                        ],
                        "description" => [
                            "The description field must be at least 25 characters."
                        ],
                        "start_at" => [
                            "The start at field must be a date after or equal to today."
                        ],
                        "end_at" => [
                            "The end at field must be a date after start at."
                        ],
                        "status" => [
                            "The selected status is invalid."
                        ]
                    ]
                ]);
   }


   public function testUpdateProject_withInvalidCredentials_returnsBadResponse()
   {

    $response = $this->withToken($this->token)->json('PUT', '/api/v1/projects/10',[
        'title' => "as",
        'description' => "ad",
        'start_at' => "2000-10-30",
        'end_at' =>  "1999-11-30",
        'status' => "nothing"
    ]);

    $response->assertStatus(404)
                ->assertJson([
                    "message" => "This project does not exist"
                ]);
   }

   public function testDeleteProject_withValidInformation_returnsSuccessResponse()
    {
        $project = Project::factory()->create(['user_id' => $this->userId]);

        $response = $this->withToken($this->token)->json('DELETE', '/api/v1/projects/'.$project->id);

        $response->assertStatus(204);
   }

   public function testDeleteProject_withInvalidInformation_returnsBadResponse()
    {

        $response = $this->withToken($this->token)->json('DELETE', '/api/v1/projects/80');

        $response->assertStatus(404)
                ->assertJson([
                    'message' => 'This project does not exist'
                ]);
   }

   


    
}
