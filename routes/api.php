<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\MessageController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/channels/{channel}/messages', [MessageController::class, 'store']);
    Route::post('/messages', [MessageController::class, 'store']);
    Route::apiResource('channels.messages', MessageController::class)->except(['store']);
});

Route::post('/messages/{message}/react', [MessageController::class, 'react']);