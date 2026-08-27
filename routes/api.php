<?php

use App\Http\Controllers\FormController;
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
