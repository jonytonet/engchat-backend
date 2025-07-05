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
        Schema::table('conversations', function (Blueprint $table) {
            // Adicionar campos de controle de fila
            $table->timestamp('queue_entry_time')->nullable()->after('tags');
            $table->timestamp('bot_handoff_time')->nullable()->after('queue_entry_time');
            $table->timestamp('first_human_response_time')->nullable()->after('bot_handoff_time');
            $table->integer('current_queue_position')->nullable()->after('first_human_response_time');

            // Adicionar subject que pode estar faltando
            $table->string('subject')->nullable()->after('category_id');

            // Índices
            $table->index('queue_entry_time');
            $table->index('current_queue_position');
            $table->index('bot_handoff_time');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('conversations', function (Blueprint $table) {
            // Remove indexes first
            $table->dropIndex(['queue_entry_time']);
            $table->dropIndex(['current_queue_position']);
            $table->dropIndex(['bot_handoff_time']);

            // Remove columns
            $table->dropColumn([
                'queue_entry_time',
                'bot_handoff_time',
                'first_human_response_time',
                'current_queue_position',
                'subject'
            ]);
        });
    }
};
