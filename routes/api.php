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
// Route::post("/logout",[AuthController::class,'logout']);


Route::middleware('auth.signature')->post('/logout', [AuthController::class, 'logout']);


Route::post("forgot-password",[AuthController::class,'forgotPassword']);
Route::post("reset-password/{token}",[AuthController::class,'resetPassword']);