<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('investor_applications', function (Blueprint $table) {
            $table->id();
            // Nullable, filled in right after insert once the real
            // auto-increment id is known (see FormController::investor()).
            // Multiple NULLs don't violate a unique index in MySQL, unlike
            // a shared placeholder string would under concurrent requests.
            $table->string('reference')->nullable()->unique();
            $table->string('investor_name');
            $table->string('investor_email');
            $table->string('investor_phone', 50);
            $table->string('investor_city', 100);
            $table->string('investor_org')->nullable();
            $table->string('investor_linkedin')->nullable();
            $table->string('sectors_of_interest', 500);
            $table->string('ticket_size', 100);
            $table->string('preferred_stage', 100);
            $table->string('experience', 100);
            $table->text('value_add')->nullable();
            $table->boolean('declaration_confidentiality')->default(false);
            $table->boolean('declaration_source_of_funds')->default(false);
            $table->string('status', 30)->default('new');
            $table->text('admin_notes')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('investor_applications');
    }
};
