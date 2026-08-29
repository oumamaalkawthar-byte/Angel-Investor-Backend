<?php

namespace App\Http\Controllers;

use App\Models\Redirect;

class RedirectController extends Controller
{
    /**
     * Public, read-only, unauthenticated by design — consumed by the Astro
     * site's astro.config.mjs at `npm run build` time, never by a visitor's
     * own browser. A backend outage just means the next build keeps
     * whatever redirects it already had.
     */
    public function index()
    {
        return response()->json(
            Redirect::where('is_active', true)
                ->get(['from_path', 'to_path'])
                ->map(fn (Redirect $r) => ['from' => $r->from_path, 'to' => $r->to_path])
                ->values()
        );
    }
}
