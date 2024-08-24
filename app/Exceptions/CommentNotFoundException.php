<?php

namespace App\Exceptions;

use Exception;

class CommentNotFoundException extends Exception
{
    public function render($request)
    {
        return response()->json([
            'message' => 'This comment does not exist'
        ], 404);
    }
}
