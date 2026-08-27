<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;

class SiteSettingController extends Controller
{
    /**
     * Public, read-only, unauthenticated by design — this is the same content
     * Astro would otherwise hardcode at build time, not sensitive data. Astro's
     * frontmatter calls this during `npm run build`; nothing here is fetched
     * by the visitor's own browser.
     */
    public function show(string $group)
    {
        return response()->json(SiteSetting::group($group));
    }
}
