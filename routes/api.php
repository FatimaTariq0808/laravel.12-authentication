<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// Default test route
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Custom API route
Route::post('/login', [AuthController::class, 'login']);
Route::post('/register', [AuthController::class, 'register']);
