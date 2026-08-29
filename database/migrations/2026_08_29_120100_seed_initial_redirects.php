<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // The redirects that previously lived hardcoded in astro.config.mjs -
        // carried over here so the backend becomes the single source of
        // truth going forward, editable without a code deploy.
        $redirects = [
            '/apply' => '/apply-as-startup',
            '/blog/what-is-an-angel-investor-guide-for-pakistani-founders' => '/blog/what-are-angel-investors',
            '/blog/angel-investors-in-pakistan-complete-guide-to-startup-funding' => '/blog/angel-investors-pakistan',
            '/blog/angel-investors-in-pakistan-top-networks-funding-platforms-2026' => '/blog/top-angel-investors-in-pakistan',
            '/blog/angel-investors-in-pakistan' => '/blog/top-angel-investors-in-pakistan',
        ];

        $now = now();

        foreach ($redirects as $from => $to) {
            DB::table('redirects')->updateOrInsert(
                ['from_path' => $from],
                ['to_path' => $to, 'status_code' => 301, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now]
            );
        }
    }

    public function down(): void
    {
        //
    }
};
