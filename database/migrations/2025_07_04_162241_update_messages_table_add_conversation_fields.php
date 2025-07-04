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
        Schema::table('messages', function (Blueprint $table) {
            // Link to conversation
            $table->unsignedBigInteger('conversation_id')->nullable()->after('id');

            // Message metadata
            $table->enum('sender_type', ['user', 'contact', 'bot', 'system'])->default('user')->after('conversation_id');
            $table->enum('message_type', ['text', 'image', 'audio', 'video', 'document', 'location', 'contact'])->default('text')->after('sender_type');
            $table->text('content')->nullable()->after('message_type');
            $table->json('metadata')->nullable()->after('content');

            // Delivery tracking
            $table->timestamp('delivered_at')->nullable()->after('is_read');

            // Reply functionality
            $table->unsignedBigInteger('reply_to_id')->nullable()->after('delivered_at');
            $table->boolean('is_internal')->default(false)->after('reply_to_id');

            // Foreign keys
            $table->foreign('conversation_id')->references('id')->on('conversations')->onDelete('cascade');
            $table->foreign('reply_to_id')->references('id')->on('messages')->onDelete('set null');

            // Indexes
            $table->index('conversation_id');
            $table->index('sender_type');
            $table->index('message_type');
            $table->index('is_internal');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            // Drop foreign keys first
            $table->dropForeign(['conversation_id']);
            $table->dropForeign(['reply_to_id']);

            // Drop indexes
            $table->dropIndex(['conversation_id']);
            $table->dropIndex(['sender_type']);
            $table->dropIndex(['message_type']);
            $table->dropIndex(['is_internal']);

            // Drop columns
            $table->dropColumn([
                'conversation_id',
                'sender_type',
                'message_type',
                'content',
                'metadata',
                'delivered_at',
                'reply_to_id',
                'is_internal'
            ]);
        });
    }
};
