<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ResetPasswordController extends Controller
{

    public function sendResetLinkEmail (Request $request)
    {
        $request->validate(['email' => ['required', 'email', 'exists:users']]);
 
        $status = Password::sendResetLink(
            $request->only('email')
        );
    
        return $status === Password::RESET_LINK_SENT ? response()->json([
            'message' => __($status)
        ], 200) : response()->json([
            'message' => __($status)
        ],400);
    }

    

}
