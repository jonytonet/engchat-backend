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
        Schema::create('conversations', function (Blueprint $table) {
            $table->id();

            // Foreign keys
            $table->unsignedBigInteger('contact_id');
            $table->unsignedBigInteger('channel_id');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->unsignedBigInteger('category_id')->nullable();

            // Status and priority
            $table->enum('status', ['open', 'pending', 'closed'])->default('open');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');

            // Ratings and metrics
            $table->integer('satisfaction_rating')->nullable();
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('closed_at')->nullable();
            $table->integer('first_response_time')->nullable()->comment('Tempo em segundos');
            $table->integer('resolution_time')->nullable()->comment('Tempo em segundos');

            // Additional data
            $table->json('tags')->nullable();
            $table->boolean('is_bot_handled')->default(false);

            $table->timestamps();

            // Foreign key constraints
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
            $table->foreign('channel_id')->references('id')->on('channels')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
            $table->foreign('category_id')->references('id')->on('categories')->onDelete('set null');

            // Indexes for performance
            $table->index('contact_id');
            $table->index('channel_id');
            $table->index('assigned_to');
            $table->index('status');
            $table->index('priority');
            $table->index('started_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversations');
    }
};
