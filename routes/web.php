<?php

use Illuminate\Support\Facades\Route;
use Source\Dashboard\Presentation\Http\Controllers\View\DashboardController;
use Source\Users\Presentation\Http\Controllers\View\LogoutController;
use Source\Users\Presentation\Http\Controllers\View\LoginController;

Route::get('/api-docs', function () {
    return view('docs.api');
});
Route::get('/', function () {
    return view('welcome');
});

Route::get('/login', function () {
    return view('panel.login');
})->name('panel.login');

Route::middleware(['guest'])->group(function () {
    Route::get('/login', function () {
        return view('panel.login');
    })->name('login');

    Route::post('/login', LoginController::class)->name('login.post');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/panel/dashboard', DashboardController::class)->name('panel.dashboard');

    Route::post('/logout', LogoutController::class)->name('logout');
});
