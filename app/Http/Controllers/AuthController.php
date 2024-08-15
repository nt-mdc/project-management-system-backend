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

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }

    public function login (Request $request)
    {
        $data = $request->validate([
            'email' => $this->emailExists(true),
            'password' => $this->password()
        ]);

        $user = User::where('email', $data['email'])->first();

        if(!Auth::attempt($data)){
            return response([
                'message' => 'Bad creds'
            ], 401);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token
        ];
    }

    public function logout (Request $request)
    {
        $user = $request->user();

        $user->tokens()->delete();

        return response()->noContent();
    }


}
