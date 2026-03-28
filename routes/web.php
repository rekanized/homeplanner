<?php

use Illuminate\Support\Facades\Route;

Route::get('/login', \App\Livewire\Auth\Login::class)->name('login');
Route::get('/setup', \App\Livewire\Auth\InitialSetup::class)->name('setup.index');

Route::middleware('auth')->group(function () {
    Route::get('/', \App\Livewire\Home\Dashboard::class)->name('home');
    Route::get('/economy', \App\Livewire\Economy\EconomyManager::class)->name('economy.index');
    Route::get('/economy/savings', \App\Livewire\Economy\Savings::class)->name('economy.savings');
    Route::get('/economy/history', \App\Livewire\Economy\MonthlyHistory::class)->name('economy.history');
    Route::get('/economy/savings-history', \App\Livewire\Economy\SavingsHistory::class)->name('economy.savings-history');
    Route::get('/shopping', \App\Livewire\Shopping\ShoppingManager::class)->name('shopping.index');
    Route::get('/todo', \App\Livewire\Todo\TodoManager::class)->name('todo.index');

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
