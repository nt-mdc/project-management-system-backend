<?php

use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::group(['controller' => UserController::class], function () {
    Route::get('reset-password', 'resetPasswordLoad');
    Route::post('reset-password', 'resetPassword');
});