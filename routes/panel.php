<?php

use Illuminate\Support\Facades\Route;
use Source\Pages\Presentation\Http\Controllers\ChangePageDetailsController;
use Source\Pages\Presentation\Http\Controllers\CreatePageController;
use Source\Pages\Presentation\Http\Controllers\ListPagesController;
use Source\Pages\Presentation\Http\Controllers\PageDetailsController;
use Source\Users\Presentation\Http\Controllers\LoginController;

Route::get('pages', ListPagesController::class);
Route::post('pages', CreatePageController::class);
Route::patch('pages/{pageId}', ChangePageDetailsController::class);
Route::get('pages/{pageId}', PageDetailsController::class);

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('login', LoginController::class);
        Route::get('me');
        Route::post('logout');
    });
});
