<?php

use App\Http\Controllers\Auth\GoogleAuthController;
use App\Http\Controllers\ChoreProofController;
use App\Http\Middleware\AdminMiddleware;
use App\Livewire\Admin\AppVersions;
use App\Livewire\Admin\AuditLogList;
use App\Livewire\Admin\UserList;
use App\Livewire\Auth\InitialSetup;
use App\Livewire\Auth\Login;
use App\Livewire\Economy\EconomyManager;
use App\Livewire\Economy\MonthlyHistory;
use App\Livewire\Economy\Savings;
use App\Livewire\Economy\SavingsHistory;
use App\Livewire\Home\Dashboard;
use App\Livewire\Kids\KidsManager;
use App\Livewire\Shopping\ShoppingManager;
use App\Livewire\Todo\TodoManager;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/login', Login::class)->name('login');
Route::get('/setup', InitialSetup::class)->name('setup.index');

Route::middleware('auth')->group(function () {
    Route::get('/', Dashboard::class)->name('home');
    Route::get('/economy', EconomyManager::class)->name('economy.index');
    Route::get('/economy/savings', Savings::class)->name('economy.savings');
    Route::get('/economy/history', MonthlyHistory::class)->name('economy.history');
    Route::get('/economy/savings-history', SavingsHistory::class)->name('economy.savings-history');
    Route::get('/shopping', ShoppingManager::class)->name('shopping.index');
    Route::get('/todo', TodoManager::class)->name('todo.index');
    Route::get('/kids', KidsManager::class)->name('kids.index');
    Route::get('/kids/proofs/{chore}', ChoreProofController::class)
        ->name('kids.proofs.show');

    Route::prefix('admin')->middleware(AdminMiddleware::class)->group(function () {
        Route::get('/users', UserList::class)->name('admin.users');
        Route::get('/settings', function () {
            return view('admin.settings');
        });
        Route::get('/logs', AuditLogList::class)->name('admin.logs');
        Route::get('/versions', AppVersions::class)->name('admin.versions');
    });

    Route::post('/logout', function () {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect('/');
    })->name('logout');
});

// Google OAuth Routes (Public)
Route::middleware('throttle:30,1')->group(function () {
    Route::get('/auth/google/redirect', [GoogleAuthController::class, 'redirect'])->name('auth.google.redirect');
    Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('auth.google.callback');
});
