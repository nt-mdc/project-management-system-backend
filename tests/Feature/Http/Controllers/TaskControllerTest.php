<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class TaskControllerTest extends TestCase
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

    public function testIndexTask_withValidInformation_returnsSuccessResponse()
    {
        $project = Project::factory()->create(['user_id' => $this->userId]);
        $task = Task::factory()->create(['user_id' => $this->userId, 'project_id' => $project->id, 'assigned_email' => $this->userEmail]);

        $response = $this->withToken($this->token)->json('GET', '/api/v1/projects/'.$project->id.'/tasks');

        $response->assertStatus(200)
                ->assertJsonStructure([[
                        'id',
                        'user_id',
                        'project_id',
                        'title',
                        'description',
                        'start_at',
                        'end_at',
                        'priority',
                        'status',
                        'assigned_email',
                        'created_at',
                        'updated_at',
                        'comments'
                ]]);
   }

   public function testIndexTask_withInvalidInformation_returnsBadResponse()
    {
        $response = $this->withToken($this->token)->json('GET', '/api/v1/projects/20/tasks');

        $response->assertStatus(404)
        ->assertJson([
            "message" => "This project does not exist",
        ]);
   }

    public function testStoreTask_withValidInformation_returnsSuccessResponse()
    {
        $project = Project::factory()->create(['user_id' => $this->userId]);

        $data = [
            'title' => Str::random(10),
            'description' => Str::random(30),
            'start_at' => "2040-10-30",
            'end_at' =>  "2040-11-30",
            'status' => "available-soon",
            'priority' => "low",
            'assigned_email' => $this->userEmail,
        ];

        $response = $this->withToken($this->token)->json('POST', '/api/v1/projects/'.$project->id.'/tasks', $data);

        $response->assertStatus(201)
                ->assertJsonStructure([
                        'title',
                        'description',
                        'start_at',
                        'end_at',
                        'status',
                        'priority',
                        'assigned_email',
                        'project_id',
                        'user_id',
                        'updated_at',
                        'created_at',
                        'id'
                ]);
   }


   public function testStoreTask_withMissingTitle_returnsBadResponse()
    {
        $project = Project::factory()->create(['user_id' => $this->userId]);

        $data = [
            'description' => Str::random(30),
            'start_at' => "2040-10-30",
            'end_at' =>  "2040-11-30",
            'status' => "available-soon",
            'priority' => "low",
            'assigned_email' => $this->userEmail,
        ];

        $response = $this->withToken($this->token)->json('POST', '/api/v1/projects/'.$project->id.'/tasks', $data);

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


    public function testStoreTask_withMissingDescription_returnsBadResponse()
    {
        $project = Project::factory()->create(['user_id' => $this->userId]);

        $data = [
            'title' => Str::random(10),
            'start_at' => "2040-10-30",
            'end_at' =>  "2040-11-30",
            'status' => "available-soon",
            'priority' => "low",
            'assigned_email' => $this->userEmail,
        ];

        $response = $this->withToken($this->token)->json('POST', '/api/v1/projects/'.$project->id.'/tasks', $data);

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

    public function testStoreTask_withMissingStart_returnsBadResponse()
    {
        $project = Project::factory()->create(['user_id' => $this->userId]);

        $data = [
            'title' => Str::random(10),
            'description' => Str::random(30),
            'end_at' =>  "2040-11-30",
            'status' => "available-soon",
            'priority' => "low",
            'assigned_email' => $this->userEmail,
        ];

        $response = $this->withToken($this->token)->json('POST', '/api/v1/projects/'.$project->id.'/tasks', $data);

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


    public function testStoreTask_withMissingEnd_returnsBadResponse()
    {
        $project = Project::factory()->create(['user_id' => $this->userId]);

        $data = [
            'title' => Str::random(10),
            'description' => Str::random(30),
            'start_at' => "2040-10-30",
            'status' => "available-soon",
            'priority' => "low",
            'assigned_email' => $this->userEmail,
        ];

        $response = $this->withToken($this->token)->json('POST', '/api/v1/projects/'.$project->id.'/tasks', $data);

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

    public function testStoreTask_withMissingStatus_returnsBadResponse()
    {
        $project = Project::factory()->create(['user_id' => $this->userId]);

        $data = [
            'title' => Str::random(10),
            'description' => Str::random(30),
            'start_at' => "2040-10-30",
            'end_at' =>  "2040-11-30",
            'priority' => "low",
            'assigned_email' => $this->userEmail,
        ];

        $response = $this->withToken($this->token)->json('POST', '/api/v1/projects/'.$project->id.'/tasks', $data);

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

    public function testStoreTask_withMissingPriority_returnsBadResponse()
    {
        $project = Project::factory()->create(['user_id' => $this->userId]);

        $data = [
            'title' => Str::random(10),
            'description' => Str::random(30),
            'start_at' => "2040-10-30",
            'end_at' =>  "2040-11-30",
            'status' => "available-soon",
            'assigned_email' => $this->userEmail,
        ];

        $response = $this->withToken($this->token)->json('POST', '/api/v1/projects/'.$project->id.'/tasks', $data);

        $response->assertStatus(422)
        ->assertJson([
            "message" => "The priority field is required.",
            "errors" => [
                "priority" => [
                    "The priority field is required."
                ]
            ]
        ]);
    }

    public function testStoreTask_withMissingAssignedEmail_returnsBadResponse()
    {
        $project = Project::factory()->create(['user_id' => $this->userId]);

        $data = [
            'title' => Str::random(10),
            'description' => Str::random(30),
            'start_at' => "2040-10-30",
            'end_at' =>  "2040-11-30",
            'status' => "available-soon",
            'priority' => "low",
        ];

        $response = $this->withToken($this->token)->json('POST', '/api/v1/projects/'.$project->id.'/tasks', $data);

        $response->assertStatus(422)
        ->assertJson([
            "message" => "The assigned email field is required.",
            "errors" => [
                "assigned_email" => [
                    "The assigned email field is required."
                ]
            ]
        ]);
    }

    public function testStoreProject_withMissingAllFields_returnsBadResponse()
    {

        $project = Project::factory()->create(['user_id' => $this->userId]);

        $response = $this->withToken($this->token)->json('POST', '/api/v1/projects/'.$project->id.'/tasks');

        $response->assertStatus(422)
        ->assertJson([
            "message" => "The title field is required. (and 6 more errors)",
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
                ],
                "priority" => [
                    "The priority field is required."
                ],
                "assigned_email" => [
                    "The assigned email field is required."
                ]
            ]
        ]);
    }

    public function testGetOneTask_withValidInformation_returnsSuccessResponse()
    {
        $project = Project::factory()->create(['user_id' => $this->userId]);
        $task = Task::factory()->create(['user_id' => $this->userId, 'project_id' => $project->id, 'assigned_email' => $this->userEmail]);

        $response = $this->withToken($this->token)->json('GET', '/api/v1/projects/'.$project->id.'/tasks/'.$task->id);


        $response->assertStatus(200)
                ->assertJsonStructure([
                    'id',
                    'user_id',
                    'project_id',
                    'title',
                    'description',
                    'start_at',
                    'end_at',
                    'priority',
                    'status',
                    'assigned_email',
                    'created_at',
                    'updated_at',
                    'comments'
                ]);
   }

    public function testGetOneTask_withInvalidProjectInformation_returnsBadResponse()
    {

        $response = $this->withToken($this->token)->json('GET', '/api/v1/projects/1/tasks/1');;

        $response->assertStatus(404)
                ->assertJson([
                    'message' => 'This project does not exist'
                ]);
    }

    public function testGetOneTask_withInvalidTaskInformation_returnsBadResponse()
    {
        $project = Project::factory()->create(['user_id' => $this->userId]);
        $response = $this->withToken($this->token)->json('GET', '/api/v1/projects/'.$project->id.'/tasks/1');;

        $response->assertStatus(404)
                ->assertJson([
                    'message' => 'This task does not exist'
                ]);
    }

   public function testUpdateTask_withValidInformation_returnsSuccessResponse()
   {
        $project = Project::factory()->create(['user_id' => $this->userId]);
        $task = Task::factory()->create(['user_id' => $this->userId, 'project_id' => $project->id, 'assigned_email' => $this->userEmail]);

        $response = $this->withToken($this->token)->json('PUT', '/api/v1/projects/'.$project->id.'/tasks/'.$task->id,[
            'title' => Str::random(12),
            'description' => Str::random(31),
            'status' => 'in-progress'
        ]);

        $response->assertStatus(200)
                    ->assertJsonStructure([
                        'id',
                        'user_id',
                        'project_id',
                        'title',
                        'description',
                        'start_at',
                        'end_at',
                        'priority',
                        'status',
                        'assigned_email',
                        'created_at',
                        'updated_at',
                    ]);
   }

   public function testUpdateTask_withInvalidInformation_returnsBadResponse()
   {
        $project = Project::factory()->create(['user_id' => $this->userId]);
        $task = Task::factory()->create(['user_id' => $this->userId, 'project_id' => $project->id, 'assigned_email' => $this->userEmail]);

        $response = $this->withToken($this->token)->json('PUT', '/api/v1/projects/'.$project->id.'/tasks/'.$task->id,[
            'title' => "as",
            'description' => "ad",
            'start_at' => "2000-10-30",
            'end_at' =>  "1999-11-30",
            'status' => "nothing",
            'priority' => "none",
            'assigned_email' => "example1234@email.com"
        ]);

        $response->assertStatus(422)
                    ->assertJson([
                        "message" => "The title field must be at least 10 characters. (and 6 more errors)",
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
                            ],
                            "priority" => [
                                "The selected priority is invalid."
                            ],
                            "assigned_email" => [
                                "The selected assigned email is invalid."
                            ]
                        ]
                    ]);
   }


   public function testUpdateTask_withInvalidProjectCredentials_returnsBadResponse()
   {
        $response = $this->withToken($this->token)->json('PUT', '/api/v1/projects/10/tasks/1',[
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

    public function testUpdateTask_withInvalidTaskCredentials_returnsBadResponse()
    {

        $project = Project::factory()->create(['user_id' => $this->userId]);
        $response = $this->withToken($this->token)->json('PUT', '/api/v1/projects/'.$project->id.'/tasks/1',[
            'title' => "as",
            'description' => "ad",
            'start_at' => "2000-10-30",
            'end_at' =>  "1999-11-30",
            'status' => "nothing"
        ]);

        $response->assertStatus(404)
                    ->assertJson([
                        "message" => "This task does not exist"
                    ]);
    }

    public function testDeleteTask_withValidInformation_returnsSuccessResponse()
    {
        $project = Project::factory()->create(['user_id' => $this->userId]);
        $task = Task::factory()->create(['user_id' => $this->userId, 'project_id' => $project->id, 'assigned_email' => $this->userEmail]);

        $response = $this->withToken($this->token)->json('DELETE', '/api/v1/projects/'.$project->id.'/tasks/'.$task->id);

        $response->assertStatus(204);
   }

    public function testDeleteTask_withInvalidProjectInformation_returnsBadResponse()
    {

        $response = $this->withToken($this->token)->json('DELETE', '/api/v1/projects/1/tasks/1');

        $response->assertStatus(404)
                ->assertJson([
                    'message' => 'This project does not exist'
                ]);
    }

    public function testDeleteTask_withInvalidInformation_returnsBadResponse()
    {
        $project = Project::factory()->create(['user_id' => $this->userId]);
        $response = $this->withToken($this->token)->json('DELETE', '/api/v1/projects/'.$project->id.'/tasks/1');

        $response->assertStatus(404)
                ->assertJson([
                    'message' => 'This task does not exist'
                ]);
    }

}
