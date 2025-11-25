<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PostController;
use App\Http\Controllers\PostViewController;
use App\Http\Controllers\ProfileViewController;

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::middleware('auth')->group(function () {
    Route::get('/posts/create', [PostController::class, 'create'])->name('posts.create');
    Route::post('/posts', [PostController::class, 'store'])->name('posts.store');
    Route::get('/@{username}/drafts', [PostController::class, 'drafts'])->name('posts.drafts');
    Route::get('/posts/{post}/edit', [PostController::class, 'edit'])->name('posts.edit');
    Route::put('/posts/{post}', [PostController::class, 'update'])->name('posts.update');
});

Route::get('/@{username}/posts/{slug}', [PostViewController::class, 'show'])->name('posts.show');

// Public profile route
Route::get('/profile/{user}', [ProfileViewController::class, 'show'])->name('profile.show');

Route::middleware('auth')->group(function () {
    Route::get('/@{username}/settings', [ProfileController::class, 'edit'])->name('settings.edit');
    Route::patch('/@{username}/settings', [ProfileController::class, 'update'])->name('settings.update');
    Route::delete('/@{username}/settings', [ProfileController::class, 'destroy'])->name('settings.destroy');
});

require __DIR__.'/auth.php';
