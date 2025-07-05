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
        Schema::create('message_attachments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('message_id')->constrained()->onDelete('cascade');

            // File information
            $table->string('file_name');
            $table->string('file_path', 500);
            $table->string('original_name')->nullable();
            $table->bigInteger('file_size'); // bytes
            $table->string('mime_type', 100);

            // Media specific fields
            $table->string('thumbnail_path', 500)->nullable();
            $table->integer('duration')->nullable()->comment('Duration in seconds for audio/video');
            $table->json('dimensions')->nullable()->comment('Width/height for images/videos');

            // Security and processing
            $table->boolean('is_scanned')->default(false);
            $table->json('scan_result')->nullable();
            $table->enum('status', ['uploading', 'processing', 'ready', 'failed'])->default('uploading');

            $table->timestamps();

            // Indexes
            $table->index('message_id');
            $table->index('mime_type');
            $table->index('status');
            $table->index('is_scanned');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_attachments');
    }
};
