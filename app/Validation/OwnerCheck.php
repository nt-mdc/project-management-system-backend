<?php

namespace App\Validation;

trait OwnerCheck
{

    protected function checkOwnerProjectUser($project, $userId)
    {
        if($project->user_id != $userId){
            return response([
                'message' => 'This project does not belong to you'
            ], 401);
        }
    }

    protected function checkOwnerProjectTask($project, $task)
    {
        if($project->id != $task->project_id){
            return response([
                'message' => 'This task does not belong to this project'
            ], 401); 
        }
    }

    protected function checkOwnerCommentUser($comment, $userId)
    {
        if($comment->user_id != $userId){
            return response([
                'message' => 'This comment does not belong to you'
            ], 401);
        }
    }

}