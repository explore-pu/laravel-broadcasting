<?php

use App\Http\Controllers\CommonController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;

//Route::get('/', function () {
//    return inertia('Welcome', [
//        'canLogin' => Route::has('login'),
//        'canRegister' => Route::has('register'),
//        'laravelVersion' => Application::VERSION,
//        'phpVersion' => PHP_VERSION,
//    ]);
//});

Route::middleware('auth')->group(function () {
    Route::get('/', [CommonController::class, 'chats'])->name('chats');

    Route::get('/contacts', [CommonController::class, 'contacts'])->name('contacts');

    Route::post('/chat', [CommonController::class, 'chat'])->name('chat');

    Route::get('/friends', [CommonController::class, 'friends'])->name('friends');

    Route::get('/messages/friend/{id}', [MessageController::class, 'friends'])->name('message.friends');

    Route::post('/messages/friend/{id}', [MessageController::class, 'friend'])->name('message.friend');

    Route::get('/messages/group/{id}', [MessageController::class, 'groups'])->name('message.groups');

    Route::post('/messages/group/{id}', [MessageController::class, 'group'])->name('message.group');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
