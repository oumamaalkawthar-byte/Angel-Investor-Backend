<?php

use App\Http\Controllers\FormController;
use App\Http\Controllers\RedirectController;
use App\Http\Controllers\SiteSettingController;
use Illuminate\Support\Facades\Route;

// Matches the frontend's existing fetch() contract exactly ({success, message,
// reference?} JSON) — only the URL changes from the old plain-PHP endpoints.
// 5 submissions per 10 minutes per IP, same limit the old SQLite-based rate
// limiter enforced.
Route::middleware('throttle:5,10')->group(function () {
    Route::post('/submit-contact', [FormController::class, 'contact']);
    Route::post('/submit-investor', [FormController::class, 'investor']);
    Route::post('/submit-startup', [FormController::class, 'startup']);
});

// Read-only page content Astro's build fetches at `npm run build` time — see
// SiteSettingController's own doc comment for why this is safely unauthenticated.
Route::get('/site-settings/{group}', [SiteSettingController::class, 'show']);

// Same build-time-only contract as site-settings — see RedirectController.
Route::get('/redirects', [RedirectController::class, 'index']);
