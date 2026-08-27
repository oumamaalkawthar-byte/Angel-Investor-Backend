<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Generic key/value content store for editable page copy — same pattern as
 * the sibling faithfuture project's SiteSetting, kept deliberately identical
 * so it's immediately familiar. Astro's build-time frontmatter fetches these
 * from GET /api/site-settings/{group} (see routes/api.php) and falls back to
 * its own hardcoded copy when a key is missing, so a typo or an unset value
 * here can never break the build.
 */
class SiteSetting extends Model
{
    protected $guarded = [];
    protected $casts = ['value' => 'json'];

    public static function get(string $key, $default = null)
    {
        $setting = static::where('key', $key)->first();
        if (!$setting) {
            return $default;
        }
        $val = $setting->value;
        if ($val === null || $val === '' || $val === []) {
            return $default;
        }
        return $val;
    }

    public static function set(string $key, $value, string $group = 'general'): void
    {
        static::updateOrCreate(['key' => $key], ['value' => $value, 'group' => $group]);
    }

    /** Values shaped for a Filament form's ->fill(), keyed by setting key. */
    public static function forForm(string|array $groups): array
    {
        return static::whereIn('group', (array) $groups)->pluck('value', 'key')->toArray();
    }

    /** Values shaped for the public API response — same values, just named for that use. */
    public static function group(string $group): array
    {
        return static::where('group', $group)->pluck('value', 'key')->toArray();
    }
}
