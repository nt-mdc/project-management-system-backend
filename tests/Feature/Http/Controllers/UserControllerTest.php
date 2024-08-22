<?php

namespace Tests\Feature\Http\Controllers;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Tests\TestCase;

class UserControllerTest extends TestCase
{

    use RefreshDatabase;
    
    public function testRegister_withValidCredentials_returnsSuccessResponse()
    {
        $data = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
            'password' => '@adm1234J'
        ];

        $response = $this->postJson('/api/auth/register', $data);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'message',
                    'user' => [
                        'name',
                        'email',
                        'updated_at',
                        'created_at',
                        'id'
                    ],
                ]);

        $this->assertDatabaseHas('users', [
            'email' => $data['email'],
        ]);
    }

    public function testRegister_withInvalidCredentials_returnsErrorResponse()
    {
        $data = [
            'name' => 'Test User',
            'email' => 'testuserexample.com',
            'password' => 'a'
        ];

        $response = $this->postJson('/api/auth/register', $data);

        $response->assertStatus(422)
                ->assertJson([
                    "message" => "The email field must be a valid email address. (and 4 more errors)",
                    "errors" => [
                        "email" => [
                            "The email field must be a valid email address."
                        ],
                        "password" => [
                            "The password field must be at least 8 characters.",
                            "The password field must contain at least one uppercase and one lowercase letter.",
                            "The password field must contain at least one symbol.",
                            "The password field must contain at least one number."
                        ]
                    ]
                ]);
    }

    public function testRegister_withMissingEmail_returnsBadRequestResponse()
    {
        $data = [
            'name' => 'Test User',
            'password' => '@adm1234J'
        ];

        $response = $this->postJson('/api/auth/register', $data);

        $response->assertStatus(422)
                ->assertJson([
                    "message" => "The email field is required.",
                    "errors" => [
                        "email" => [
                            "The email field is required."
                        ],
                    ]
                ]);
    }

    public function testRegister_withMissingName_returnsBadRequestResponse()
    {
        $data = [
            'email' => 'testuser@example.com',
            'password' => '@adm1234J'
        ];

        $response = $this->postJson('/api/auth/register', $data);

        $response->assertStatus(422)
                ->assertJson([
                    "message" => "The name field is required.",
                    "errors" => [
                        "name" => [
                            "The name field is required."
                        ],
                    ]
                ]);
    }

    public function testRegister_withMissingPassword_returnsBadRequestResponse()
    {
        $data = [
            'name' => 'Test User',
            'email' => 'testuser@example.com',
        ];

        $response = $this->postJson('/api/auth/register', $data);

        $response->assertStatus(422)
                ->assertJson([
                    "message" => "The password field is required.",
                    "errors" => [
                        "password" => [
                            "The password field is required."
                        ],
                    ]
                ]);
    }


    public function testLogin_withValidCredentials_returnsSuccessResponse()
    {
        $user = User::factory()->create([
            'name' => 'John Dk',
            'email' => 'test@example.com',
            'password' => Hash::make('@123admJ'),
        ]);

        $data = [
            'email' => 'test@example.com',
            'password' => '@123admJ',
        ];

        $response = $this->json('POST', '/api/auth/login', $data);

        $response->assertStatus(200)
            ->assertJsonStructure([
                "message",
                "token" => [
                    "access_token",
                    "token_type"
                ],
                "user" => [
                    "id",
                    "name",
                    "email",
                    "created_at",
                    "updated_at",
                ]
            ]);
    }

    public function testLogin_withInvalidCredentials_returnsErrorResponse()
    {
        $user = User::factory()->create([
            'name' => 'John Dk',
            'email' => 'test@example.com',
            'password' => Hash::make('@123admJ'),
        ]);

        $data = [
            'email' => 'teswadst@example.com',
            'password' => '@1dw23admJ',
        ];

        $response = $this->json('POST', '/api/auth/login', $data);

        $response->assertStatus(422)
            ->assertJson([
                "message" => "The selected email is invalid.",
                "errors" => [
                    "email" => [
                        "The selected email is invalid."
                    ]
                ]
            ]);

    }

    public function testLogin_withMissingEmail_returnsBadRequestResponse()
    {
        $user = User::factory()->create([
            'name' => 'John Dk',
            'email' => 'test@example.com',
            'password' => Hash::make('@123admJ'),
        ]);

        $data = [
            'password' => '@123admJ',
        ];

        $response = $this->json('POST', '/api/auth/login', $data);

        $response->assertStatus(422)
                ->assertJson([
                    "message" => "The email field is required.",
                    "errors" => [
                        "email" => [
                            "The email field is required."
                        ],
                    ]
                ]);

    }

    public function testLogin_withMissingPassword_returnsBadRequestResponse()
    {
        $user = User::factory()->create([
            'name' => 'John Dk',
            'email' => 'test@example.com',
            'password' => Hash::make('@123admJ'),
        ]);

        $data = [
            'email' => 'test@example.com'
        ];

        $response = $this->json('POST', '/api/auth/login', $data);

        $response->assertStatus(422)
                ->assertJson([
                    "message" => "The password field is required.",
                    "errors" => [
                        "password" => [
                            "The password field is required."
                        ],
                    ]
                ]);

    }

    public function testLogout_withValidToken_returnsSuccessRequestResponse()
    {

        $user = User::factory()->create([
            'password' => Hash::make('@123admJ'),
        ]);

        $response = $this->post('/api/auth/login', [
            'email' => $user->email,
            'password' => '@123admJ',
        ]);

        $token = $response->json('token.access_token');

        $response = $this->withToken($token)->json('DELETE', '/api/auth/logout');

        $response->assertStatus(204);
    }

    public function testLogout_withInvalidToken_returnsBadRequestResponse()
    {
        $response = $this->withToken(Str::random(10))->json('DELETE', '/api/auth/logout');

        $response->assertStatus(401);
    }


    public function testForgetPassword_withValidCredentials_returnsSuccessRequestResponse()
    {

        $user = User::factory()->create([
            'password' => Hash::make('@123admJ'),
        ]);

        $data = [
            'email' => $user->email
        ];

        $response = $this->json('POST', '/api/auth/password/email', $data);

        $response->assertStatus(200)
                ->assertJson([
                    "message" => "Please check your mail to reset your password",
                ]);
    }

   

    public function testForgetPassword_withInvalidCredentials_returnsBadRequestResponse()
    {

        $user = User::factory()->create([
            'password' => Hash::make('@123admJ'),
        ]);

        $data = [
            'email' => "fjrbsfs@ejbs.com"
        ];

        $response = $this->json('POST', '/api/auth/password/email', $data);

        $response->assertStatus(422)
                ->assertJson([
                    "message" => "The selected email is invalid.",
                    "errors" => [
                        "email" => [
                            "The selected email is invalid."
                        ],
                    ],
                ]);
    }

    public function testForgetPassword_withMissingEmail_returnsBadRequestResponse()
    {

        $user = User::factory()->create([
            'password' => Hash::make('@123admJ'),
        ]);

        $data = [
        ];

        $response = $this->json('POST', '/api/auth/password/email', $data);

        $response->assertStatus(422)
                ->assertJson([
                    "message" => "The email field is required.",
                    "errors" => [
                        "email" => [
                            "The email field is required."
                        ],
                    ]
                ]);
    }

    public function testProfile_withValidToken_returnsSuccessRequestResponse()
    {

        $user = User::factory()->create([
            'password' => Hash::make('@123admJ'),
        ]);

        $response = $this->post('/api/auth/login', [
            'email' => $user->email,
            'password' => '@123admJ',
        ]);

        $token = $response->json('token.access_token');

        $response = $this->withToken($token)->json('GET', '/api/v1/user/profile');

        $response->assertStatus(200)
                ->assertJsonStructure([
                    "user" => [
                        "id",
                        "name",
                        "email",
                        "created_at",
                        "updated_at",
                    ],
                    "profile_photo_url",
                ]);
    }

    public function testProfile_withInvalidToken_returnsBadRequestResponse()
    {

        $response = $this->withToken(Str::random(15))->json('GET', '/api/v1/user/profile');

        $response->assertStatus(401)
                ->assertJson([
                    "message" => "Unauthenticated."
                ]);
    }

    public function testUpdateUser_withValidCredentials_returnsSuccessRequestResponse()
    {

        $user = User::factory()->create([
            'password' => Hash::make('@123admJ'),
        ]);

        $response = $this->post('/api/auth/login', [
            'email' => $user->email,
            'password' => '@123admJ',
        ]);

        $token = $response->json('token.access_token');

        $response = $this->withToken($token)->json('PUT', '/api/v1/user/update', [
            'email' => 'newemail@user.com',
            'name' => 'New user name'
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    "user" => [
                        "id",
                        "name",
                        "email",
                        "created_at",
                        "updated_at",
                    ]
                ]);
    }

    public function testUpdateUser_withInvalidCredentials_returnsBadRequestResponse()
    {
        $response = $this->withToken(Str::random(15))->json('PUT', '/api/v1/user/update', [
            'email' => 'newemail@user.com',
            'name' => 'New user name'
        ]);

        $response->assertStatus(401)
                ->assertJson([
                    "message" => "Unauthenticated."
                ]);
    }

    public function testUpdateUser_withValidCredentialsAndInvalidEmail_returnsBadRequestResponse()
    {

        $user = User::factory()->create([
            'password' => Hash::make('@123admJ'),
        ]);

        $response = $this->post('/api/auth/login', [
            'email' => $user->email,
            'password' => '@123admJ',
        ]);

        $token = $response->json('token.access_token');

        $response = $this->withToken($token)->json('PUT', '/api/v1/user/update', [
            'email' => 'newemailuser.com',
            'name' => 'New user name'
        ]);

        $response->assertStatus(422)
                ->assertJson([
                    "message" => "The email field must be a valid email address.",
                    "errors" => [
                        "email" => [
                            "The email field must be a valid email address."
                        ],
                    ]
                ]);
    }

    public function testStoreProfile_withValidCredentials_returnsSuccessRequestResponse()
    {

        $user = User::factory()->create([
            'password' => Hash::make('@123admJ'),
        ]);

        $response = $this->post('/api/auth/login', [
            'email' => $user->email,
            'password' => '@123admJ',
        ]);

        $token = $response->json('token.access_token');

        $response = $this->withToken($token)->json('POST', '/api/v1/user/profile-photo/store-or-update', [
            'base64' => "data:image/png;base64,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",
        ]);

        $response->assertStatus(200)
                ->assertJsonStructure([
                    "profile_photo" => [
                        "user_id",
                        "url",
                        "id"
                    ]
                ]);
    }

    public function testStoreProfile_withInvalidCredentials_returnsBadRequestResponse()
    {

        $user = User::factory()->create([
            'password' => Hash::make('@123admJ'),
        ]);

        $response = $this->post('/api/auth/login', [
            'email' => $user->email,
            'password' => '@123admJ',
        ]);

        $token = $response->json('token.access_token');

        $response = $this->withToken($token)->json('POST', '/api/v1/user/profile-photo/store-or-update');

        $response->assertStatus(422)
                ->assertJson([
                    "message" => "The base64 field is required.",
                    "errors" => [
                        "base64" => [
                            "The base64 field is required."
                        ],
                    ]
                ]);
    }

    public function testDeleteProfile_withValidCredentials_returnsSuccessRequestResponse()
    {

        $user = User::factory()->create([
            'password' => Hash::make('@123admJ'),
        ]);

        $response = $this->post('/api/auth/login', [
            'email' => $user->email,
            'password' => '@123admJ',
        ]);

        $token = $response->json('token.access_token');

        $response = $this->withToken($token)->json('POST', '/api/v1/user/profile-photo/store-or-update', [
            'base64' => "data:image/png;base64,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",
        ]);

        $response = $this->withToken($token)->json('DELETE', '/api/v1/user/profile-photo/delete');

        $response->assertStatus(204);


    }

    public function testDeleteProfile_withInvalidCredentials_returnsBadRequestResponse()
    {
        $response = $this->withToken(Str::random(15))->json('DELETE', '/api/v1/user/profile-photo/delete');

        $response->assertStatus(401);
    }

}
