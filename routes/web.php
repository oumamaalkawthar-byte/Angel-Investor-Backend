<?php

use App\Models\BlogPost;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return response()->json(['service' => 'Angel Investor backend', 'status' => 'ok']);
});

// A rough approximation of the live Astro post page, so an editor can sanity
// check a post before publishing — not pixel-identical to the real site
// (that's a separate Astro build), but shows real heading/table/FAQ
// structure and content. Auth-gated: only logged-in admin users can view it,
// since a draft's content shouldn't be publicly reachable.
Route::get('/blog-preview/{blogPost}', function (BlogPost $blogPost) {
    // Checked manually rather than via the `auth` middleware alias, since
    // that redirects to a named `login` route this app doesn't define
    // (Filament's own login lives at /admin/login, a different guard setup).
    if (!auth()->check()) {
        abort(403);
    }

    return view('blog-preview', ['post' => $blogPost]);
})->name('blog.preview');

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
