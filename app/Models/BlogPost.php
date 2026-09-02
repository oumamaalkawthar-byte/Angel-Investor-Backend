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
}
