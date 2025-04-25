<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Default test route
Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

// Custom API route
Route::get('/hello', function () {
    return response()->json(['message' => 'Hello from API!']);
});
