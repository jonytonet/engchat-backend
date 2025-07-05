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
        Schema::create('conversation_assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->onDelete('cascade');

            // Atribuição
            $table->foreignId('assigned_to')->constrained('users')->onDelete('cascade');
            $table->foreignId('assigned_by')->nullable()->constrained('users')->onDelete('set null')
                ->comment('NULL se foi automático');
            $table->enum('assignment_type', ['automatic', 'manual', 'transfer', 'escalation']);

            // Motivo e contexto
            $table->string('assignment_reason')->nullable();
            $table->integer('queue_position_before')->nullable();
            $table->integer('wait_time_before')->nullable()->comment('Tempo de espera antes da atribuição em minutos');

            // Dados do agente na atribuição
            $table->string('agent_status_at_assignment', 50)->nullable();
            $table->integer('agent_conversations_count')->nullable();
            $table->foreignId('agent_department_id')->nullable()->constrained('departments')->onDelete('set null');

            // Status da atribuição
            $table->enum('status', ['active', 'transferred', 'completed', 'abandoned'])->default('active');

            // Timestamps
            $table->timestamp('assigned_at')->useCurrent();
            $table->timestamp('accepted_at')->nullable()->comment('Quando o agente aceitou');
            $table->timestamp('completed_at')->nullable();

            // Resultado
            $table->string('completion_reason')->nullable();
            $table->integer('customer_satisfaction')->nullable()->comment('1-5 rating');

            $table->timestamps();

            // Indexes
            $table->index('conversation_id');
            $table->index('assigned_to');
            $table->index('assignment_type');
            $table->index('status');
            $table->index('assigned_at');
            $table->index('agent_department_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_assignments');
    }
};
