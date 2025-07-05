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
        Schema::create('conversation_queue', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->onDelete('cascade');

            // Informações da fila
            $table->foreignId('department_id')->constrained()->onDelete('cascade');
            $table->foreignId('category_id')->nullable()->constrained()->onDelete('set null');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');

            // Posição e timing
            $table->integer('queue_position');
            $table->integer('estimated_wait_time')->nullable()->comment('Tempo estimado em minutos');

            // Tentativas de atribuição
            $table->integer('assignment_attempts')->default(0);
            $table->timestamp('last_assignment_attempt')->nullable();

            // Controle de notificações
            $table->timestamp('last_position_notification')->nullable();
            $table->integer('notification_count')->default(0);

            // Status
            $table->enum('status', ['waiting', 'assigned', 'expired', 'cancelled'])->default('waiting');
            $table->foreignId('assigned_to')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('assigned_at')->nullable();

            // Metadados
            $table->timestamp('entered_queue_at')->useCurrent();
            $table->timestamp('removed_from_queue_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->unique('conversation_id', 'unique_conversation_queue');
            $table->index('department_id');
            $table->index('category_id');
            $table->index('priority');
            $table->index('queue_position');
            $table->index('status');
            $table->index('entered_queue_at');
            $table->index('assigned_to');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_queue');
    }
};
