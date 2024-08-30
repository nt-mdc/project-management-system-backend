<?php

namespace App\Exceptions;

use Exception;

class ProfilePhotoNotFoundException extends Exception
{
    public function render($request)
    {
        return response()->json([
            'message' => 'This photo does not exist'
        ], 404);
    }
}
