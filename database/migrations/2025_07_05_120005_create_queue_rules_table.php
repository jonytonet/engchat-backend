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
        Schema::create('queue_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('department_id')->constrained()->onDelete('cascade');

            // Configurações da fila
            $table->integer('max_queue_size')->default(100);
            $table->integer('max_wait_time_minutes')->default(60);

            // Intervalos de notificação
            $table->integer('first_notification_after_minutes')->default(2);
            $table->integer('notification_interval_minutes')->default(5);
            $table->integer('max_notifications')->default(10);

            // Templates de mensagem
            $table->foreignId('welcome_template_id')->nullable()->constrained('message_templates')->onDelete('set null');
            $table->foreignId('position_update_template_id')->nullable()->constrained('message_templates')->onDelete('set null');
            $table->foreignId('timeout_template_id')->nullable()->constrained('message_templates')->onDelete('set null');
            $table->foreignId('assigned_template_id')->nullable()->constrained('message_templates')->onDelete('set null');

            // Regras de priorização
            $table->json('priority_rules')->nullable()->comment('Regras para priorização automática');
            $table->boolean('vip_priority_enabled')->default(true);

            // Horários de funcionamento
            $table->json('working_hours')->nullable();
            $table->foreignId('out_of_hours_template_id')->nullable()->constrained('message_templates')->onDelete('set null');

            // Auto-assignment
            $table->boolean('auto_assignment_enabled')->default(true);
            $table->enum('assignment_algorithm', ['round_robin', 'least_busy', 'skill_based'])->default('least_busy');

            // Escalação
            $table->boolean('escalation_enabled')->default(true);
            $table->integer('escalation_time_minutes')->default(15);
            $table->foreignId('escalation_department_id')->nullable()->constrained('departments')->onDelete('set null');

            // Metadados
            $table->boolean('is_active')->default(true);
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Indexes
            $table->unique('department_id', 'unique_department_rules');
            $table->index('is_active');
            $table->index('auto_assignment_enabled');
            $table->index('escalation_enabled');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('queue_rules');
    }
};
