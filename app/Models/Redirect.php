<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Redirect extends Model
{
    protected $guarded = [];

    protected $casts = [
        'status_code' => 'integer',
        'is_active'   => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (Redirect $redirect) {
            // from_path is always a path on this site, so its domain (if pasted with
            // one) is stripped for a consistent key. to_path is left as typed — it
            // may legitimately be a full external URL.
            $redirect->from_path = static::normalize($redirect->from_path);
            $redirect->to_path = trim($redirect->to_path);
        });
    }

    /** Leading slash, no trailing slash, no domain — so lookups are consistent regardless of how a path was typed. */
    public static function normalize(string $path): string
    {
        $path = trim($path);
        $path = preg_replace('#^https?://[^/]+#i', '', $path) ?? $path;
        $path = '/' . ltrim($path, '/');
        if ($path !== '/') {
            $path = rtrim($path, '/');
        }
        return $path;
    }
}
