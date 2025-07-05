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
            // Adicionar campos extras se necessário
            // Como a tabela já tem conversation_id, vamos adicionar outros campos úteis
            
            if (!Schema::hasColumn('messages', 'message_status')) {
                $table->enum('message_status', ['pending', 'sent', 'delivered', 'read', 'failed'])
                      ->default('pending')->after('is_from_contact');
            }
            
            if (!Schema::hasColumn('messages', 'channel_id')) {
                $table->foreignId('channel_id')->nullable()->constrained()->onDelete('set null')->after('conversation_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn(['message_status', 'channel_id']);
        });
    }
};
