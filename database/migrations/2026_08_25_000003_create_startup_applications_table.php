<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('startup_applications', function (Blueprint $table) {
            $table->id();
            // Nullable, filled in right after insert once the real
            // auto-increment id is known (see FormController::startup()).
            // Multiple NULLs don't violate a unique index in MySQL, unlike
            // a shared placeholder string would under concurrent requests.
            $table->string('reference')->nullable()->unique();
            $table->string('founder_name');
            $table->string('founder_email');
            $table->string('founder_phone', 50);
            $table->string('founder_city', 100);
            $table->text('founder_bio');
            $table->string('founder_linkedin')->nullable();
            $table->json('cofounders')->nullable();
            $table->string('startup_name');
            $table->string('startup_website')->nullable();
            $table->string('one_liner', 120);
            $table->string('sector', 100);
            $table->string('stage', 100);
            $table->string('registration_status', 100);
            $table->string('team_size', 100)->nullable();
            $table->string('pitch_deck_path');
            $table->string('pitch_deck_original_name');
            $table->string('investment_ask', 100);
            $table->string('equity_offered', 100);
            $table->text('use_of_funds');
            $table->text('traction')->nullable();
            $table->boolean('declaration_authentic')->default(false);
            $table->boolean('declaration_ethical')->default(false);
            $table->boolean('declaration_consent')->default(false);
            $table->string('status', 30)->default('new');
            $table->text('admin_notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('startup_applications');
    }
};
