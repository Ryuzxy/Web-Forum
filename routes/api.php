<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\ProfileController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    // Message endpoints
    Route::post('/channels/{channel}/messages', [MessageController::class, 'store']);
    Route::get('/channels/{channel}/messages', [MessageController::class, 'index']);
    Route::get('/messages/{message}', [MessageController::class, 'show']);
    
    // Reaction endpoints
    Route::post('/messages/{message}/react', [ReactionController::class, 'toggleReaction']);
    Route::get('/messages/{message}/reactions', [ReactionController::class, 'getReactions']);
    
    // Invite endpoints
    Route::post('/servers/{server}/generate-invite', [InviteController::class, 'generate']);
    Route::post('/join/{code}', [InviteController::class, 'join']);
    
    // File endpoints
    Route::post('/channels/{channel}/upload', [FileUploadController::class, 'upload']);
    Route::get('/messages/{message}/download', [FileUploadController::class, 'download'])->name('files.download');
    Route::get('/messages/{message}/preview', [FileUploadController::class, 'preview'])->name('files.preview');

    Route::post('/profile/status', [ProfileController::class, 'updateStatus']);
});