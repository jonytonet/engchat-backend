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
        Schema::create('category_keywords', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained()->onDelete('cascade');

            // Keyword details
            $table->string('keyword', 100);
            $table->integer('weight')->default(1)->comment('Weight for classification algorithm');
            $table->boolean('is_exact_match')->default(false);
            $table->boolean('is_case_sensitive')->default(false);

            // Status and language
            $table->boolean('is_active')->default(true);
            $table->string('language', 10)->default('pt-BR');

            // Analytics
            $table->integer('match_count')->default(0);
            $table->decimal('success_rate', 5, 2)->default(0.00);

            $table->timestamps();

            // Indexes
            $table->index('category_id');
            $table->index('keyword');
            $table->index('weight');
            $table->index('is_active');
            $table->index('language');

            // Unique constraint
            $table->unique(['category_id', 'keyword', 'language']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('category_keywords');
    }
};
