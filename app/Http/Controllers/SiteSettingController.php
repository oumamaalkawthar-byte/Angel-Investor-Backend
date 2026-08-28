<?php

namespace App\Http\Controllers;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Storage;

class SiteSettingController extends Controller
{
    /**
     * FileUpload fields (Filament) store a path relative to their disk, not a
     * full URL — resolve the ones Astro needs to embed directly in a <meta>
     * tag so it doesn't need its own copy of this list.
     */
    private const IMAGE_KEYS = ['seo_og_image'];

    /**
     * Public, read-only, unauthenticated by design — this is the same content
     * Astro would otherwise hardcode at build time, not sensitive data. Astro's
     * frontmatter calls this during `npm run build`; nothing here is fetched
     * by the visitor's own browser.
     */
    public function show(string $group)
    {
        $values = SiteSetting::group($group);

        foreach (self::IMAGE_KEYS as $key) {
            if (!empty($values[$key])) {
                $values[$key] = Storage::disk('public')->url($values[$key]);
            }
        }

        return response()->json($values);
    }
}
