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
            'body' => $post->body,
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
