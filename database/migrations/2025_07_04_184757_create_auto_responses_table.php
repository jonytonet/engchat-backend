<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('auto_responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');

            // Trigger configuration
            $table->string('name', 100);
            $table->enum('trigger_type', ['keyword', 'welcome', 'offline', 'category'])->default('keyword');
            $table->string('trigger_keyword')->nullable();
            $table->json('trigger_conditions')->nullable();

            // Response configuration
            $table->text('response_text');
            $table->enum('response_type', ['text', 'template', 'redirect', 'transfer'])->default('text');
            $table->json('response_data')->nullable()->comment('Additional data for template/redirect');

            // Timing and conditions
            $table->integer('delay_seconds')->default(0);
            $table->json('working_hours')->nullable();
            $table->json('conditions')->nullable()->comment('Additional conditions for triggering');

            // Status and analytics
            $table->boolean('is_active')->default(true);
            $table->integer('usage_count')->default(0);
            $table->decimal('success_rate', 5, 2)->default(0.00);
            $table->integer('priority')->default(1);

            $table->timestamps();

            // Indexes
            $table->index('category_id');
            $table->index('trigger_type');
            $table->index('trigger_keyword');
            $table->index('is_active');
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_responses');
    }
};
