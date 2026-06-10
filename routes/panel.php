<?php

use Illuminate\Support\Facades\Route;
use Source\Pages\Presentation\Http\Controllers\ChangePageDetailsController;
use Source\Pages\Presentation\Http\Controllers\CreatePageController;
use Source\Pages\Presentation\Http\Controllers\ListPagesController;
use Source\Pages\Presentation\Http\Controllers\PageDetailsController;
use Source\References\Presentation\Http\Controllers\CreateReferenceController;
use Source\References\Presentation\Http\Controllers\DeleteReferenceController;
use Source\References\Presentation\Http\Controllers\ListReferencesController;
use Source\References\Presentation\Http\Controllers\UpdateReferenceController;
use Source\References\Presentation\Http\Controllers\UploadReferenceMediaController;
use Source\Sliders\Presentation\Http\Controllers\CreateSliderController;
use Source\Sliders\Presentation\Http\Controllers\DeleteSliderController;
use Source\Sliders\Presentation\Http\Controllers\ListSlidersController;
use Source\Sliders\Presentation\Http\Controllers\UpdateSliderController;
use Source\Sliders\Presentation\Http\Controllers\UploadSliderMediaController;
use Source\Users\Presentation\Http\Controllers\API\LoginController;
use Source\Users\Presentation\Http\Controllers\API\LogoutController;
use Source\Users\Presentation\Http\Controllers\API\RefreshController;

Route::get('sliders', ListSlidersController::class);
Route::post('sliders', CreateSliderController::class);
Route::patch('sliders/{sliderId}', UpdateSliderController::class);
Route::delete('sliders/{sliderId}', DeleteSliderController::class);
Route::post('sliders/{sliderId}/media', UploadSliderMediaController::class);

Route::get('references', ListReferencesController::class);
Route::post('references', CreateReferenceController::class);
Route::patch('references/{referenceId}', UpdateReferenceController::class);
Route::delete('references/{referenceId}', DeleteReferenceController::class);
Route::post('references/{referenceId}/media', UploadReferenceMediaController::class);

Route::get('pages', ListPagesController::class);
Route::post('pages', CreatePageController::class);
Route::patch('pages/{pageId}', ChangePageDetailsController::class);
Route::get('pages/{pageId}', PageDetailsController::class);

Route::prefix('v1')->group(function () {
    Route::prefix('auth')->group(function () {
        Route::post('login', LoginController::class);
        Route::get('me');
        Route::post('logout', LogoutController::class);
        Route::post('refresh', RefreshController::class);
    });
});
