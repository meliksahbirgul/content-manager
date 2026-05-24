<?php

use Illuminate\Support\Facades\Route;
use Source\Dashboard\Presentation\Http\Controllers\View\DashboardController;
use Source\Languages\Presentation\Http\Controllers\View\SwitchLanguageController;
use Source\Media\Presentation\Http\Controllers\DeleteMediaController;
use Source\Media\Presentation\Http\Controllers\UploadMediaController;
use Source\Pages\Presentation\Http\Controllers\View\CreatePageViewController;
use Source\Pages\Presentation\Http\Controllers\View\EditPageViewController;
use Source\Pages\Presentation\Http\Controllers\View\ListPagesController;
use Source\Pages\Presentation\Http\Controllers\View\StorePageController;
use Source\Pages\Presentation\Http\Controllers\View\UpdatePageController;
use Source\Users\Presentation\Http\Controllers\View\LoginController;
use Source\Users\Presentation\Http\Controllers\View\LogoutController;

Route::get('/api-docs', function () {
    return view('docs.api');
});
Route::get('/', function () {
    return view('welcome');
});

Route::middleware(['guest'])->group(function () {
    Route::get('/login', function () {
        return view('panel.login');
    })->name('login');

    Route::post('/login', LoginController::class)->name('login.post');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/panel', DashboardController::class)->name('panel.dashboard');
    Route::get('/panel/pages', ListPagesController::class)->name('panel.pages');
    Route::get('/panel/pages/create', CreatePageViewController::class)->name('panel.pages.create');
    Route::post('/panel/pages', StorePageController::class)->name('panel.pages.store');
    Route::get('/panel/pages/{pageId}/edit', EditPageViewController::class)->name('panel.pages.edit');
    Route::put('/panel/pages/{pageId}', UpdatePageController::class)->name('panel.pages.update');

    Route::post('/panel/pages/{pageId}/media', UploadMediaController::class)->name('panel.pages.media.upload');
    Route::delete('/panel/media/{mediaId}', DeleteMediaController::class)->name('panel.media.delete');

    Route::post('/panel/language', SwitchLanguageController::class)->name('panel.language.switch');
    Route::post('/logout', LogoutController::class)->name('logout');
});
