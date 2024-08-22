<?php

use App\Http\Controllers\ProjectCommentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\TaskCommentController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['prefix' => 'auth'],function () {
    Route::group(['controller' => UserController::class], function () {
        Route::post('register', 'register');
        Route::post('login', 'login');
        Route::delete('logout', 'logout')->middleware('auth:sanctum');
        Route::post('password/email', 'forgetPassword');
    });
});

Route::group(['prefix' => 'v1', 'middleware' => 'auth:sanctum'], function(){
    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('projects.tasks', TaskController::class);
    Route::apiResource('projects.comments', ProjectCommentController::class)->except(['update']);
    Route::apiResource('projects.tasks.comments', TaskCommentController::class)->except(['update']);
    
    Route::get('assigned-tasks', [TaskController::class, 'assignedTasks']);

    Route::group(['prefix' => 'user', 'controller' => UserController::class], function () {
        Route::get('profile', 'profile');
        Route::get('update', 'updateUser');
        Route::post('profile-photo/store-or-update', 'updateOrStoreProfilePhoto');
        Route::get('profile-photo/get', 'getProfilePhoto');
    });
    
});