<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['service' => 'Angel Investor backend', 'status' => 'ok']);
});

// Serve storage files without a symlink (this host doesn't reliably support
// them, same as the sibling faithfuture app) — used for pitch-deck downloads
// from the Filament admin.
Route::get('/storage/{path}', function (string $path) {
    $baseReal = realpath(storage_path('app/public'));
    if ($baseReal === false) {
        abort(404);
    }

    $fileReal = realpath(storage_path('app/public/' . $path));
    if ($fileReal === false || !str_starts_with($fileReal, $baseReal . DIRECTORY_SEPARATOR)) {
        abort(404);
    }

    return response()->file($fileReal);
})->where('path', '.*');
