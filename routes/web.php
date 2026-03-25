<?php

use Illuminate\Support\Facades\Route;

Route::get('/login', \App\Livewire\Auth\Login::class)->name('login');
Route::get('/setup', \App\Livewire\Auth\InitialSetup::class)->name('setup.index');

Route::middleware('auth')->group(function () {
    Route::get('/', function () {
        return view('economy.index');
    })->name('economy.index');

    Route::prefix('admin')->middleware(\App\Http\Middleware\AdminMiddleware::class)->group(function () {
        Route::get('/users', \App\Livewire\Admin\UserList::class)->name('admin.users');
        Route::get('/settings', function () { return view('admin.settings'); });
        Route::get('/logs', \App\Livewire\Admin\AuditLogList::class)->name('admin.logs');
    });

    Route::post('/logout', function () {
        Illuminate\Support\Facades\Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
        return redirect('/');
    })->name('logout');
});

// Google OAuth Routes (Public)
Route::get('/auth/google/redirect', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
Route::get('/auth/google/callback', [\App\Http\Controllers\Auth\GoogleAuthController::class, 'callback'])->name('auth.google.callback');
