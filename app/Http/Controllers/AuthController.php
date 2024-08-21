<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Validation\Rules;

class AuthController extends Controller
{

    use Rules;

    public function register (Request $request)
    {
        $data = $request->validate([
            'name' => $this->name(true),
            'email' => $this->emailUnique(true),
            'password' => $this->password()
        ]);

        $user = User::create($data);

        return [
            'message' => 'User registered successfully',
            'user' => $user,
        ];
    }

    public function login (Request $request)
    {
        $data = $request->validate([
            'email' => $this->emailExists(true),
            'password' => $this->password(),
        ]);

        $user = User::where('email', $data['email'])->first();

        if(Auth::attempt($data)){
            $token = $user->createToken('auth_token')->plainTextToken;

            return [
                'message' => 'Login successful',
                'token' => [
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                ],
                'user' => $user,
            ];
        }

        return response([
            'message' => 'Invalid credentials'
        ], 401);
    }

    public function logout (Request $request)
    {
        $user = $request->user();

        $user->tokens()->delete();

        return response()->noContent();
    }


}
