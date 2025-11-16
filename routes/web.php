<?php
use App\Http\Controllers\ProfileController;
use App\Http\controllers\FileUploadController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ReactionController;
use App\Http\Controllers\InviteController;
use Illuminate\Support\Facades\Route;

// Public Routes
Route::get('/', function () {
    return view('welcome');
});

// Auth Routes (Breeze)
Route::middleware(['auth', 'verified'])->group(function () {
    // Dashboard
    Route::get('/dashboard', [ServerController::class, 'index'])->name('dashboard');
    
    // Profile Routes
    Route::prefix('profile')->group(function () {
        Route::get('/', [ProfileController::class, 'show'])->name('profile.show');
        Route::get('/edit', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/', [ProfileController::class, 'update'])->name('profile.update');
        Route::patch('/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
        Route::delete('/', [ProfileController::class, 'destroy'])->name('profile.destroy');
    });
    
    // User Profile (public view)
    Route::get('/user/{username}', [ProfileController::class, 'show'])->name('profile.user');
    
    // Server Routes
    Route::resource('servers', ServerController::class);
    Route::get('/servers/{server}', [ServerController::class, 'show'])->name('servers.show');
    
    // Message Routes
    Route::post('/channels/{channel}/messages', [MessageController::class, 'store'])->name('messages.store');
    Route::get('/channels/{channel}/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::post('/messages/{message}/react', [ReactionController::class, 'toggleReaction'])->name('messages.react');
    Route::post('/messages/send', [MessageController::class, 'store'])->name('messages.send');


    // Invite Routes
    Route::prefix('servers/{server}')->group(function () {
        Route::post('/generate-invite', [InviteController::class, 'generate'])->name('invites.generate');
        Route::post('/revoke-invite', [InviteController::class, 'revoke'])->name('invites.revoke');
    });
    
    Route::prefix('invite')->group(function () {
        Route::get('/', [InviteController::class, 'showInviteForm'])->name('invites.form');
        Route::post('/process', [InviteController::class, 'processInviteCode'])->name('invites.process');
        Route::get('/{code}', [InviteController::class, 'showJoinConfirmation'])->name('invites.confirm');
        Route::post('/{code}/join', [InviteController::class, 'joinServer'])->name('invites.join');
    });
    
    Route::get('/join/{code}', [InviteController::class, 'showJoinConfirmation'])->name('invites.join.redirect');

    //file upload 
    Route::post('/channels/{channel}/upload', [FileUploadController::class, 'upload'])->name('channels.upload');
});

// Breeze Auth Routes
require __DIR__.'/auth.php';