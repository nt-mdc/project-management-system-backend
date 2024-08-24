<?php

namespace App\Exceptions;

use Exception;

class ProjectNotFoundException extends Exception
{
    public function render($request)
    {
        return response()->json([
            'message' => 'This project does not exist'
        ], 404);
    }
}
