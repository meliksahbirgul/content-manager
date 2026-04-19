<?php

use Illuminate\Support\Facades\Route;
use Source\Pages\Presentation\Http\Controllers\ChangePageDetailsController;
use Source\Pages\Presentation\Http\Controllers\CreatePageController;
use Source\Pages\Presentation\Http\Controllers\ListPagesController;

Route::get('pages', ListPagesController::class);
Route::post('pages', CreatePageController::class);
Route::patch('pages/{pageId}', ChangePageDetailsController::class);
