<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use Illuminate\Support\Facades\Storage;

/**
 * Public, read-only, unauthenticated by design - consumed by the Astro
 * site's build (getAllPosts/getPostBySlug at `npm run build` time), never
 * by a visitor's own browser. Same contract as SiteSettingController.
 */
class BlogPostController extends Controller
{
    /**
     * The rich-text editor's own image button has no alt-text field, so
     * editors list {filename, alt} pairs separately (see BlogPostResource's
     * "Body Images" section) - matched back onto the actual <img> tags here,
     * by filename, before the HTML ever reaches the Astro site.
     */
    private function applyBodyImageAlts(string $body, ?array $alts): string
    {
        if (blank($alts)) {
            return $body;
        }

        $map = collect($alts)
            ->filter(fn ($row) => filled($row['filename'] ?? null))
            ->pluck('alt', 'filename');

        if ($map->isEmpty()) {
            return $body;
        }

        return preg_replace_callback('/<img\s[^>]*src="([^"]+)"[^>]*>/i', function (array $m) use ($map) {
            $filename = basename(parse_url($m[1], PHP_URL_PATH) ?: $m[1]);
            if (!$map->has($filename)) {
                return $m[0];
            }

            $alt = e($map->get($filename));
            $tag = $m[0];

            return preg_match('/\salt="[^"]*"/i', $tag)
                ? preg_replace('/\salt="[^"]*"/i', ' alt="' . $alt . '"', $tag)
                : preg_replace('/^<img\s/i', '<img alt="' . $alt . '" ', $tag);
        }, $body) ?? $body;
    }

    private function transform(BlogPost $post): array
    {
        return [
            'title' => $post->title,
            'slug' => $post->slug,
            'description' => $post->description,
            'pubDate' => $post->pub_date->toIso8601String(),
            'author' => $post->author,
            'category' => $post->category,
            'readTime' => $post->read_time,
            'art' => $post->art,
            'image' => $post->image ? Storage::disk('public')->url($post->image) : null,
            'imageAlt' => $post->image_alt,
            'videoUrl' => $post->video_url,
            'body' => $this->applyBodyImageAlts($post->body, $post->body_image_alts),
            'faqs' => $post->faqs ?? [],
            'seoTitle' => $post->seo_title,
            'metaDescription' => $post->meta_description,
            'canonicalUrl' => $post->canonical_url,
            'nofollowExternalLinks' => $post->nofollow_external_links,
        ];
    }

    public function index()
    {
        return response()->json(
            BlogPost::published()->orderByDesc('pub_date')->get()->map(fn (BlogPost $p) => $this->transform($p))->values()
        );
    }

    public function show(string $slug)
    {
        $post = BlogPost::published()->where('slug', $slug)->first();

        if (!$post) {
            return response()->json(['message' => 'Not found'], 404);
        }

        return response()->json($this->transform($post));
    }
}
