<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ExampleController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');



Route::post('/users', [ExampleController::class, 'store']);
Route::put('/users/{id}', [ExampleController::class, 'update']);
Route::post('/check-email', [ExampleController::class, 'checkEmail']);

