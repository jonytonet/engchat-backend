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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->string('color', 20)->default('#667eea');
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->integer('priority')->default(1);
            $table->integer('estimated_time')->nullable()->comment('Tempo estimado em minutos');
            $table->json('auto_responses')->nullable();
            $table->boolean('requires_specialist')->default(false);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            // Foreign keys
            $table->foreign('parent_id')->references('id')->on('categories')->onDelete('set null');

            // Indexes
            $table->index('parent_id');
            $table->index('priority');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
