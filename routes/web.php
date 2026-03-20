<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('economy.index');
})->name('economy.index');


Route::get('/admin/users', function () {
    return view('admin.users');
});

Route::get('/admin/settings', function () {
    return view('admin.settings');
});

Route::get('/admin/logs', function () {
    return view('admin.logs');
});

