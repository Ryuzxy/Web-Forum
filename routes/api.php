<?php
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\FileUploadController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\InviteController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    // User
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Messages
    Route::prefix('channels/{channel}')->group(function () {
        Route::get('/messages', [MessageController::class, 'index']);
        Route::post('/messages', [MessageController::class, 'store']);
        Route::post('/upload', [FileUploadController::class, 'upload']);
    });

    // Message Reactions
    Route::prefix('messages/{message}')->group(function () {
        Route::get('/reactions', [ReactionController::class, 'getReactions']);
        Route::post('/react', [ReactionController::class, 'toggleReaction']);
    });

    // File Management
    Route::prefix('files')->group(function () {
        Route::get('/messages/{message}/download', [FileUploadController::class, 'download'])->name('files.download');
        Route::get('/messages/{message}/preview', [FileUploadController::class, 'preview'])->name('files.preview');
        Route::get('/files/messages/{message}/download', [FileUploadController::class, 'download'])->name('files.download');
Route::get('/files/messages/{message}/preview', [FileUploadController::class, 'preview'])->name('files.preview');
    });

    // Profile
    Route::prefix('profile')->group(function () {
        Route::post('/status', [ProfileController::class, 'updateStatus']);
    });

    // Invites
    Route::prefix('servers/{server}')->group(function () {
        Route::post('/generate-invite', [InviteController::class, 'generate']);
    });
    
    Route::post('/join/{code}', [InviteController::class, 'join']);
});