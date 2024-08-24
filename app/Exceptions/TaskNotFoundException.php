<?php

namespace App\Exceptions;

use Exception;

class TaskNotFoundException extends Exception
{
    public function render($request)
    {
        return response()->json([
            'message' => 'This task does not exist'
        ], 404);
    }
}
