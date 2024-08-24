<?php

namespace App\Validation;

trait Exists{


    protected function projectExist($project)
    {
        if (!$project) {
            throw new \App\Exceptions\ProjectNotFoundException();
        }
    
        return $project;
    }

    protected function taskExist($task){
        if(!$task){
            throw new \App\Exceptions\TaskNotFoundException();
        }

        return $task;
    }

    protected function commentExist($comment){
        if(!$comment){
            throw new \App\Exceptions\CommentNotFoundException();
        }

        return $comment;
    }

}