<?php

use App\Http\Controllers\CommonController;
use App\Http\Controllers\FriendController;
use App\Http\Controllers\GroupController;
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

    Route::get('/friend/{id}/messages', [FriendController::class, 'messages'])->name('friend.messages');

    Route::post('/friend/{id}/message', [FriendController::class, 'message'])->name('friend.message');

    Route::get('/group/{id}/messages', [GroupController::class, 'messages'])->name('group.messages');

    Route::post('/group/{id}/message', [GroupController::class, 'message'])->name('group.message');

    Route::post('/group/create', [GroupController::class, 'create'])->name('group.create');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::post('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
