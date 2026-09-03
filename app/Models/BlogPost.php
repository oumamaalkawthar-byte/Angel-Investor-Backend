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

    /** True if the body (HTML string or Tiptap's live JSON form-state) contains an H{$level} heading. */
    public static function bodyHasHeadingLevel(mixed $body, int $level): bool
    {
        if (is_string($body)) {
            return stripos($body, "<h{$level}") !== false;
        }

        if (is_array($body)) {
            $found = false;
            $walk = function ($node) use (&$found, &$walk, $level) {
                if (!is_array($node) || $found) {
                    return;
                }
                if (($node['type'] ?? null) === 'heading' && ($node['attrs']['level'] ?? null) === $level) {
                    $found = true;
                    return;
                }
                foreach ($node['content'] ?? [] as $child) {
                    $walk($child);
                }
            };
            $walk($body);
            return $found;
        }

        return false;
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
    public static function extractBodyImageFilenames(mixed $body): array
    {
        // TiptapEditor's *live* in-form state is a Tiptap JSON document (an
        // array of nested nodes), not the plain HTML string it's actually
        // saved as - so both shapes need to be handled here. Recursing the
        // JSON tree for image nodes' `src` attrs covers the live-editing
        // case; the regex covers the saved/HTML case.
        $srcs = [];

        if (is_array($body)) {
            $walk = function ($node) use (&$srcs, &$walk) {
                if (!is_array($node)) {
                    return;
                }
                if (($node['type'] ?? null) === 'image' && !empty($node['attrs']['src'])) {
                    $srcs[] = $node['attrs']['src'];
                }
                foreach ($node['content'] ?? [] as $child) {
                    $walk($child);
                }
            };
            $walk($body);
        } elseif (is_string($body)) {
            preg_match_all('/<img[^>]+src="([^"]+)"/i', $body, $matches);
            $srcs = $matches[1] ?? [];
        }

        return collect($srcs)
            ->map(fn (string $src) => basename(parse_url($src, PHP_URL_PATH) ?: $src))
            ->unique()
            ->values()
            ->all();
    }
}
