<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Validation\Rules;
use App\Models\PasswordReset;
use App\Models\ProfilePhoto;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use PhpParser\Node\Expr\FuncCall;

class UserController extends Controller
{
    use Rules;

    /*
    |   Basic auth functions:
    |   Register; Login; Logout
    */
    public function register (Request $request)
    {
        $data = $request->validate([
            'name' => $this->name(true),
            'email' => $this->emailUnique(true),
            'password' => $this->password()
        ]);

        $user = User::create($data);

        return response()->json([
            'message' => 'User registered successfully',
            'user' => $user,
        ]);
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

            return response()->json([
                'message' => 'Login successful',
                'token' => [
                    'access_token' => $token,
                    'token_type' => 'Bearer',
                ],
                'user' => $user,
            ]);
        }

        return response()->json([
            'message' => 'Invalid credentials'
        ], 401);
    }

    public function logout (Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();
        return response()->noContent();
    }

    /*
    |   Reset password functions
    */

    //This function generates a token and sends the password reset email
    public function forgetPassword (Request $request)
    {
        try {
            $user = User::where('email', $request->email)->get();

            if(count($user) > 0) {

                $token = Str::random(40);
                $domain = URL::to('/');
                $url = $domain.'/reset-password?token='.$token;

                $data['url'] = $url;
                $data['email'] = $request->email;
                $data['title'] = "Password Reset";
                $data['body'] = "Please click on below link to reset your password";

                Mail::send('forgetPasswordMail', ['data' => $data], function($message) use ($data){
                    $message->to($data['email'])->subject($data['title']);
                });

                $datetime = Carbon::now()->format('Y-m-d H:i:s');
                PasswordReset::updateOrCreate(
                    ['email' => $request->email],
                    [
                        'email' => $request->email,
                        'token' => $token,
                        'created_at' => $datetime
                    ]
                );

                return response()->json(['message' => 'Please check your mail to reset your password']);

            } else {
                return response()->json(['message' => 'User not found']);
            }

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()]);
        }

    }

    //This function validates the token and returns the web page with the reset form
    public function resetPasswordLoad (Request $request)
    {
        $reset = PasswordReset::where('token', $request->token)->get();
        if(isset($request->token) && count($reset) > 0){
            $user = User::where('email', $reset[0]['email'])->first();
            return view('resetPassword', compact('user'));
        }
        else{
            return "<h1>Not found!</h1>";
        }
    }

    //This function validates the form and changes the password
    public function resetPassword(Request $request)
    {
     
        $request->validate([
            'password' => array_merge($this->password(), ['confirmed']),
        ]);
     
        $user = User::find($request->id);
        $user->password = Hash::make($request->password);
        $user->save();

        PasswordReset::where('email', $user->email)->delete();

        return "<h1 style='color: green;'>Your password has been reset successfully.</h1>";
    }


    /*
    | Some user functions
    */

    public function profile(Request $request)
    {
        $user = $request->user();
        return response()->json([
           "user" => $user,
            "profile_photo_url" => URL::to('/').'/api/v1/profile-photo/get',
        ]);
    }

    public function updateUser(Request $request)
    {
        $data = $request->validate([
            'email' => $this->emailUnique(),
            'name' => $this->name(),
        ]);

        $user = $request->user();

        $user->fill($data);
        $user->save();

        return response()->json([
           "user" => $user,
        ]);
    }

    /*
    | Profile photo functions
    */

    public function getProfilePhoto(Request $request) 
    {
        $path = public_path()."/images/".$request->user()->photo->url;
        return response()->file($path);
    }

    public function updateOrStoreProfilePhoto(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'base64' => 'required'
        ]);

        $profilePhoto = ProfilePhoto::where('user_id', $user->id)->first();
        if($profilePhoto) {
            unlink(public_path()."/images/".$profilePhoto->url);
        }
        
        $imgBase64 = $data["base64"];
        $imgExtension = explode('/', mime_content_type($imgBase64))[1];
        $imgString = substr($imgBase64, strpos($imgBase64,",")+1);
        $image = base64_decode($imgString);
        $imgUrl = \Illuminate\Support\Str::random(15);
        file_put_contents(public_path()."/images/".$imgUrl.'.'.$imgExtension, $image);

        $profilePhoto = ProfilePhoto::updateOrCreate(
            ['user_id' => $user->id],
            [
                "user_id" => $user->id,
                "url" => $imgUrl.'.'.$imgExtension,
            ]
        );

        return response()->json([
            'success' => true,
            'profile_photo' => $profilePhoto
        ]);
    }

    public function deleteProfilePhoto(Request $request)
    {
        $user = $request->user();
        $profilePhoto = ProfilePhoto::where('user_id', $user->id)->first();
        unlink($profilePhoto->url);
        $profilePhoto->delete();

        return response()->noContent();
    }
}
