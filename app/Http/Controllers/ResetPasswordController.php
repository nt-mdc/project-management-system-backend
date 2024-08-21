<?php

namespace App\Http\Controllers;

use App\Models\PasswordReset;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use App\Validation\Rules;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class ResetPasswordController extends Controller
{

    use Rules;

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

                return response()->json(['success' => true, 'message' => 'Please check your mail to reset your password']);

            } else {
                return response()->json(['success' => false, 'message' => 'User not found']);
            }

        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()]);
        }

    }

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

    

}
