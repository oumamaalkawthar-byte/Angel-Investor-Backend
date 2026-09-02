<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    protected $guarded = [];

    protected $casts = [
        'pub_date' => 'datetime',
        'faqs' => 'array',
        'body_image_alts' => 'array',
        'nofollow_external_links' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (BlogPost $post) {
            if (blank($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
        });

        // A slug change would otherwise silently 404 the old URL (search
        // results, shared links) once the site rebuilds - auto-create a
        // redirect the same way a manual one would be added in Settings ->
        // Redirects, so this never has to be remembered by hand.
        static::updating(function (BlogPost $post) {
            if ($post->isDirty('slug') && $post->getOriginal('slug')) {
                $oldPath = '/blog/' . $post->getOriginal('slug');
                $newPath = '/blog/' . $post->slug;

                if ($oldPath !== $newPath) {
                    Redirect::updateOrCreate(
                        ['from_path' => Redirect::normalize($oldPath)],
                        ['to_path' => $newPath, 'status_code' => 301, 'is_active' => true]
                    );
                }
            }
        });
    }

    /** Published AND its publish date/time has actually arrived - lets "Published" + a future date act as scheduling. */
    public function scopePublished($query)
    {
        return $query->where('status', 'published')->where('pub_date', '<=', now());
    }

    /**
     * Filenames of every <img> currently inserted in a body HTML string (via
     * the rich-text editor's file attachments) - shown back to the editor as
     * a checklist so they know which images still need an alt-text entry in
     * the Body Images repeater, without having to dig through the editor or
     * an external file list themselves. Static so it can be called against
     * the form's live in-progress state, not just a saved model.
     */
    public static function extractBodyImageFilenames(?string $bodyHtml): array
    {
        preg_match_all('/<img[^>]+src="([^"]+)"/i', (string) $bodyHtml, $matches);

        return collect($matches[1] ?? [])
            ->map(fn (string $src) => basename(parse_url($src, PHP_URL_PATH) ?: $src))
            ->unique()
            ->values()
            ->all();
    }
}
