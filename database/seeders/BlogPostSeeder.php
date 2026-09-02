<?php

namespace Database\Seeders;

use App\Models\BlogPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;

/**
 * One-time migration of the 3 real posts that were previously authored and
 * published in Sanity, back onto this Laravel backend (see BlogPostResource /
 * BlogPostController) - run once via `artisan db:seed --class=BlogPostSeeder`.
 * Safe to re-run: uses updateOrCreate keyed on slug.
 */
class BlogPostSeeder extends Seeder
{
    public function run(): void
    {
        $dataPath = database_path('seeders/data/blog-posts.json');
        $posts = json_decode(file_get_contents($dataPath), true);

        foreach ($posts as $post) {
            $imagePath = database_path("seeders/data/images/{$post['slug']}.jpg");
            $storedImagePath = null;

            if (is_file($imagePath)) {
                $storedImagePath = "blog/{$post['slug']}.jpg";
                Storage::disk('public')->put($storedImagePath, file_get_contents($imagePath));
            }

            BlogPost::updateOrCreate(
                ['slug' => $post['slug']],
                [
                    'title' => $post['title'],
                    'description' => $post['description'],
                    'pub_date' => $post['pubDate'],
                    'author' => $post['author'] ?? 'Angel Investor',
                    'category' => $post['category'] ?? null,
                    'read_time' => $post['readTime'] ?? null,
                    'art' => $post['art'] ?? 'photo',
                    'image' => $storedImagePath,
                    'body' => $post['bodyHtml'],
                ]
            );

            $this->command?->info("Seeded: {$post['slug']}");
        }
    }
}
