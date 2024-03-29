<?php

use App\Http\Controllers\Api\Auth\LogoutController;
use App\Http\Controllers\Api\Auth\LoginUserController;
use App\Http\Controllers\Api\Auth\RegisterUserController;
use Illuminate\Support\Facades\Route;


Route::post('/register', RegisterUserController::class)
    ->middleware('guest')
    ->name('register');

Route::post('/login', LoginUserController::class)
    ->middleware('guest')
    ->name('login');

Route::post('/logout', LogoutController::class)
    ->middleware('auth:sanctum')
    ->name('logout');
