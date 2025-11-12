<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\FileUploadController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/channels/{channel}/messages', [MessageController::class, 'store']);
    Route::post('/messages', [MessageController::class, 'store']);
    Route::apiResource('channels.messages', MessageController::class)->except(['store']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/api/servers/{server}/generate-invite', [InviteController::class, 'generate']);
    Route::post('/api/join/{code}', [InviteController::class, 'join']);
    Route::post('/messages/{message}/react', [ReactionController::class, 'toggleReaction']);
    Route::get('/messages/{message}/reactions', [ReactionController::class, 'getReactions']);
});

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/channels/{channel}/upload', [FileUploadController::class, 'upload']);
    Route::get('/messages/{message}/download', [FileUploadController::class, 'download'])->name('files.download');
    Route::get('/messages/{message}/preview', [FileUploadController::class, 'preview'])->name('files.preview');
});