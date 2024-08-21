<?php

use App\Http\Controllers\ResetPasswordController;
use Illuminate\Support\Facades\Route;

Route::group(['controller' => ResetPasswordController::class], function () {
    Route::get('reset-password', 'resetPasswordLoad');
    Route::post('reset-password', 'resetPassword');
});