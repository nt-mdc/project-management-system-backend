<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProjectCommentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskCommentController;
use App\Http\Controllers\TaskController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

Route::group(['controller' => AuthController::class],function () {
    Route::post('register', 'register');
    Route::post('login', 'login');
    Route::delete('logout', 'logout')->middleware('auth:sanctum');
});


Route::group(['prefix' => 'v1', 'middleware' => 'auth:sanctum'], function(){
    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('projects.tasks', TaskController::class);
    Route::apiResource('projects.comments', ProjectCommentController::class)->except(['update']);
    Route::apiResource('projects.tasks.comments', TaskCommentController::class)->except(['update']);
    
    Route::get('assigned-tasks', [TaskController::class, 'assignedTasks']);
});