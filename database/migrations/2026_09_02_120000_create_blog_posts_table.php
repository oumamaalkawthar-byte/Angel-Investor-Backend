<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('description');
            $table->dateTime('pub_date');
            $table->string('author')->default('Angel Investor');
            $table->string('category')->nullable();
            $table->string('read_time')->nullable();
            $table->string('art')->default('photo');
            $table->string('image')->nullable();
            $table->string('video_url')->nullable();
            $table->longText('body');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
