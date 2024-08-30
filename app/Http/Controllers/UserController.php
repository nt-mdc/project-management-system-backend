<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use App\Validation\Rules;
use App\Models\PasswordReset;
use App\Models\ProfilePhoto;
use App\Validation\Exists;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use PhpParser\Node\Expr\FuncCall;

/**
 * @group User management
 *
 * This API provides comprehensive authentication management capabilities, including user registration, login, and logout operations.
 * Its endpoints allow for secure user authentication, issuance of access tokens, ensuring data protection and adherence to security best practices.
 *
 */

class UserController extends Controller
{
    use Rules, Exists;

    /**
     * 
     * Create a user.
     * 
     * 
     * @bodyParam name string required The name of the user. Example: John Doe
     * @bodyParam email string required The email of the user and this email must to be unique. Example: john.doe@example.com
     * @bodyParam password string required The user's password. Must be at least 8 characters long, including letters, numbers, symbols, and at least one uppercase and one lowercase letter. Example: @123User
     *
     * @response 201 {
     *   "message": "User registered successfully",
     *   "user": {
     *       "name": "John Doe",
     *       "email": "john.doe@example.com",
     *       "updated_at": "2024-08-24T13:50:23.000000Z",
     *       "created_at": "2024-08-24T13:50:23.000000Z",
     *       "id": 78
     *   }
     * }
     *
     * @response 422 {
     *    "message": "The name field must be a string. (and 5 more errors)",
     *    "errors": {
     *        "name": [
     *            "The name field must be a string."
     *        ],
     *        "email": [
     *            "The email has already been taken."
     *        ],
     *        "password": [
     *            "The password field must be at least 8 characters.",
     *            "The password field must contain at least one uppercase and one lowercase letter.",
     *            "The password field must contain at least one symbol.",
     *            "The password field must contain at least one number."
     *        ]
     *    }
     * }
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
        ], 201);
    }

    /**
     * Login the user.
     * 
     * It is at this endpoint that you will obtain the Bearer token to access other routes that require authentication.
     * 
     * 
     * @unauthenticated
     *
     * @bodyParam email string required The email of the user. Example: john.doe@example.com
     * @bodyParam password string required The user's password. Must be at least 8 characters long, including letters, numbers, symbols, and at least one uppercase and one lowercase letter. Example: "@123User"
     *
     * @response 200 {
     *    "message": "Login successful",
     *    "token": {
     *        "access_token": "token_value",
     *        "token_type": "Bearer"
     *    },
     *    "user": {
     *        "id": 78,
     *        "name": "John Doe",
     *        "email": "john.doe@example.com",
     *        "created_at": "2024-08-24T13:50:23.000000Z",
     *        "updated_at": "2024-08-24T13:50:23.000000Z"
     *    }
     * }
     *
     * @response 401 {
     *       "message": "Invalid credentials"
     * }
     * 
     * @response 422 {
     *       "message": "The selected email is invalid.",
     *    "errors": {
     *        "email": [
     *            "The selected email is invalid."
     *        ],
     *        "password": [
     *            "The password field must be at least 8 characters.",
     *            "The password field must contain at least one uppercase and one lowercase letter.",
     *            "The password field must contain at least one symbol.",
     *            "The password field must contain at least one number."
     *        ]
     *    }
     * }
    */

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

    /**
     * 
     * Log out the user.
     * 
     * On this endpoint you log out the user and remove their Bearer token.
     * 
     * 
     * @authenticated
     * 
     * @response 204 scenario="No content"
     * 
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * 
     */

    public function logout (Request $request)
    {
        $user = $request->user();
        $user->tokens()->delete();
        return response()->noContent();
    }

    /**
     * 
     * Change a user's password.
     * 
     * This endpoint generates and sends the email to reset the user's password.
     *  
     * @unauthenticated
     *
     * @bodyParam email string required The email of the user requesting password reset. Example: john.doe@example.com
     *
     * @response 200 {
     *   "message": "Please check your mail to reset your password"
     * }
     *
     * @response 422 {
     *   "message": "User not found"
     * }
     *
     * @response 400 {
     *   "message": "Error message"
     * }
     */
    public function forgetPassword (Request $request)
    {
        $data = $request->validate([
            'email' => $this->emailExists(true),
        ]);

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
                return response()->json(['message' => 'User not found'], 422);
            }

        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }

    }

    //WEB PAGE -> This function validates the token and returns the web page with the reset form
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

    //WEB PAGE -> This function validates the form and changes the password
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


    /**
     * User Profile Retrieval.
     * 
     * Retrieves detailed information from the authenticated user's profile, including personal data and profile picture.
     * 
     * @group Account Management
     *
     * The Account Management encompasses endpoints dedicated to managing user profiles and profile pictures.
     * It includes operations for viewing and updating profile information as well as managing the associated profile picture.
     * 
     * @authenticated
     *
     * @response 200 {
     *   "user": {
     *     "id": 1,
     *     "name": "John Doe",
     *     "email": "john.doe@example.com",
     *     "created_at": "2024-08-24T13:50:23.000000Z",
     *     "updated_at": "2024-08-24T13:50:23.000000Z"
     *   },
     *   "profile_photo_url": "http://yourdomain.com/api/v1/user/profile-photo/get"
     * }
     * 
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     */

    public function profile(Request $request)
    {
        $user = $request->user();
        return response()->json([
            "user" => $user,
            "profile_photo_url" => URL::to('/').'/api/v1/user/profile-photo/get',
        ]);
    }

    /**
     * 
     * Update the user information
     * 
     * Updates the authenticated user's profile information, allowing modifications to personal data 
     * 
     * @group Account Management
     * 
     * @authenticated
     *
     * @bodyParam email string The new email of the user. Example: john.newemail@example.com
     * @bodyParam name string The new name of the user. Example: John Smith
     *
     * @response 200 {
     *   "id": 1,
     *   "name": "John Smith",
     *   "email": "john.newemail@example.com",
     *   "created_at": "2024-08-24T13:50:23.000000Z",
     *   "updated_at": "2024-08-24T13:50:23.000000Z"
     * }
     *
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * 
     * @response 422 {
     *    "message": "The name field is required. (and 1 more error)",
     *    "errors": {
     *        "name": [
     *            "The name field is required."
     *        ],
     *        "email": [
     *            "The email has already been taken."
     *        ]
     *    }
     * }
     */

    public function updateUser(Request $request)
    {
        $data = $request->validate([
            'email' => $this->emailUnique(),
            'name' => $this->name(),
        ]);

        $user = $request->user();

        $user->fill($data);
        $user->save();

        return response()->json($user);
    }

    /**
     * Retrieve Profile Picture.
     * 
     * Retrieves the authenticated user's profile picture.
     * 
     * @group Account Management
     *
     * APIs for managing user profile photos.
     * 
     * @authenticated
     *
     * @response file The user's profile photo.
     * 
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     */
    public function getProfilePhoto(Request $request) 
    {
        $path = public_path()."/images/".$request->user()->photo->url;
        return response()->file($path);
    }

    /**
     * Update or Store Profile Picture.
     * 
     * Updates the authenticated user's profile picture or stores a new image if none exists.
     * 
     * @group Account Management
     * 
     * @authenticated
     *
     * @bodyParam base64 string required The base64-encoded image string. Example: data:image/png;base64,iVBORw...
     *
     * @response 201 {
     *   "id": 1,
     *   "user_id": 1,
     *   "url": "randomstring.png"
     * }
     * 
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * 
     * @response 422 {
     *   "message": "The base64 field is required.",
     *   "errors": {
     *     "base64": ["The base64 field is required."]
     *   }
     * }
     */

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

        return response()->json($profilePhoto, 201);
    }

    /**
     * Delete Profile Picture.
     * 
     * Deletes the authenticated user's profile picture.
     * 
     * @group Account Management
     * 
     * @authenticated
     * 
     * @response 204
     * 
     * @response 401 {
     *   "message": "Unauthenticated."
     * }
     * 
     * @response 404 scenario="Project not found"{
     *   "message": "This project does not exist"
     * }
     */

    public function deleteProfilePhoto(Request $request)
    {
        $user = $request->user();
        $profilePhoto = $this->photoExist(ProfilePhoto::where('user_id', $user->id)->first());
        unlink(public_path()."/images/".$profilePhoto->url);
        $profilePhoto->delete();

        return response()->noContent();
    }
}
