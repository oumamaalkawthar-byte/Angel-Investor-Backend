<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->string('status')->default('draft')->after('slug'); // draft | published
            $table->string('image_alt')->nullable()->after('image');
            $table->string('seo_title')->nullable()->after('description');
            $table->string('meta_description', 160)->nullable()->after('seo_title');
            $table->string('canonical_url')->nullable()->after('meta_description');
            $table->boolean('nofollow_external_links')->default(true)->after('video_url');
            $table->json('faqs')->nullable()->after('body');
        });

        // Adding `status` with a "draft" default backfills every existing row
        // to draft too - which would instantly unpublish the 3 real posts
        // already live (BlogPostController only returns status=published).
        // Every post that exists at the moment this migration runs got here
        // via the earlier BlogPostSeeder, i.e. is already-live content, so
        // mark it published; anything created afterward goes through the
        // Filament form, which defaults new posts to Draft explicitly.
        DB::table('blog_posts')->update(['status' => 'published']);
    }

    public function down(): void
    {
        Schema::table('blog_posts', function (Blueprint $table) {
            $table->dropColumn([
                'status', 'image_alt', 'seo_title', 'meta_description',
                'canonical_url', 'nofollow_external_links', 'faqs',
            ]);
        });
    }
};
