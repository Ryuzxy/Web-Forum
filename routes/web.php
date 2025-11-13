<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\InviteController;
use App\Http\Controllers\ReactionController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    Route::get('/profile/show', [ProfileController::class, 'show'])->name('profile.show');
    Route::put('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.password.update');
    Route::get('/user/{username}', [ProfileController::class, 'show'])->name('profile.user');
});

Route::resource('servers', ServerController::class)->middleware('auth');

Route::get('/dashboard', [ServerController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::get('/servers/{server}', [ServerController::class, 'show'])
    ->name('servers.show')
    ->middleware('auth');


// Route::middleware('auth')->group(function () {
//     Route::post('/channels/{channel}/messages', [MessageController::class, 'store'])->name('messages.store');
//     Route::get('/channels/{channel}/messages', [MessageController::class, 'index'])->name('messages.index');
//     Route::post('/messages/{message}/react', [ReactionController::class, 'toggleReaction'])->name('messages.react');
// });

// INVITE ROUTES
Route::post('/servers/{server}/generate-invite', [InviteController::class, 'generate'])
    ->name('invites.generate');
    
Route::middleware('auth')->group(function () {
    Route::post('/servers/{server}/generate-invite', [InviteController::class, 'generate'])
        ->name('invites.generate');
    
    Route::post('/servers/{server}/revoke-invite', [InviteController::class, 'revoke'])
        ->name('invites.revoke');
        
    Route::get('/invite', [InviteController::class, 'showInviteForm'])
        ->name('invites.form');
        
    Route::post('/invite/process', [InviteController::class, 'processInviteCode'])
        ->name('invites.process');
        
    Route::get('/invite/{code}', [InviteController::class, 'showJoinConfirmation'])
        ->name('invites.confirm');
        
    Route::post('/invite/{code}/join', [InviteController::class, 'joinServer'])
        ->name('invites.join');
        
    Route::get('/join/{code}', [InviteController::class, 'showJoinConfirmation'])
        ->name('invites.join.redirect');
});

Route::middleware('auth')->group(function () {
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/profile/{username}', [ProfileController::class, 'show'])->name('profile.show');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::patch('/profile/password', [ProfileController::class, 'updatePassword'])->name('profile.update-password');
    Route::post('/profile/status', [ProfileController::class, 'updateStatus'])->name('profile.update-status');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Get online users (for sidebar/etc)
    Route::get('/api/users/online', [ProfileController::class, 'getOnlineUsers'])->name('users.online');
});

require __DIR__.'/auth.php';
