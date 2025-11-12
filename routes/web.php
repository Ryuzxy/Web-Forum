<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ServerController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\InviteController;
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
});
Route::resource('servers', ServerController::class)->middleware('auth');

Route::get('/dashboard', [ServerController::class, 'index'])
    ->middleware(['auth'])
    ->name('dashboard');

Route::get('/servers/{server}', [ServerController::class, 'show'])
    ->name('servers.show')
    ->middleware('auth');

Route::post('/messages', [MessageController::class, 'store'])->name('messages.store');

// Route::post('/messages/{message}/react', [MessageController::class, 'toggleReaction']);

Route::post('/servers/{server}/generate-invite', [InviteController::class, 'generate'])
    ->name('invites.generate');
    
Route::middleware('auth')->group(function () {
    // Generate and revoke invites
    Route::post('/servers/{server}/generate-invite', [InviteController::class, 'generate'])
        ->name('invites.generate');
    
    Route::post('/servers/{server}/revoke-invite', [InviteController::class, 'revoke'])
        ->name('invites.revoke');
        
    // 🔹 SHOW FORM untuk input code (TANPA parameter)
    Route::get('/invite', [InviteController::class, 'showInviteForm'])
        ->name('invites.form');
        
    // 🔹 PROCESS code dari form input
    Route::post('/invite/process', [InviteController::class, 'processInviteCode'])
        ->name('invites.process');
        
    // 🔹 SHOW JOIN CONFIRMATION (DENGAN code parameter)
    Route::get('/invite/{code}', [InviteController::class, 'showJoinConfirmation'])
        ->name('invites.confirm');
        
    // 🔹 JOIN SERVER (form submission dari confirmation page)
    Route::post('/invite/{code}/join', [InviteController::class, 'joinServer'])
        ->name('invites.join');
        
    // 🔹 DIRECT JOIN LINK (untuk shareable links)
    Route::get('/join/{code}', [InviteController::class, 'showJoinConfirmation'])
        ->name('invites.join.redirect');
});

require __DIR__.'/auth.php';
