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
        Schema::create('queue_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
            $table->foreignId('queue_id')->constrained('conversation_queue')->onDelete('cascade');

            // Tipo de notificação
            $table->enum('notification_type', ['position_update', 'wait_time_update', 'agent_assigned', 'queue_timeout']);

            // Conteúdo da notificação
            $table->unsignedBigInteger('message_template_id')->nullable();
            $table->text('custom_message')->nullable();
            $table->json('variables')->nullable()->comment('Variáveis para o template');

            // Dados da fila no momento
            $table->integer('queue_position_at_time');
            $table->integer('estimated_wait_time')->nullable();
            $table->integer('total_queue_size');

            // Status de envio
            $table->enum('status', ['pending', 'sent', 'failed'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();

            // Controle
            $table->timestamp('scheduled_at');
            $table->timestamps();

            // Indexes
            $table->index('conversation_id');
            $table->index('queue_id');
            $table->index('notification_type');
            $table->index('status');
            $table->index('scheduled_at');
            $table->index('sent_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queue_notifications');
    }
};
