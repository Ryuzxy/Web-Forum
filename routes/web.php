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

Route::post('/servers/{server}/generate-invite', [InviteController::class, 'generate'])
    ->name('invites.generate');
    
Route::get('/join/{code}', [InviteController::class, 'join'])
    ->name('invites.join');

require __DIR__.'/auth.php';
