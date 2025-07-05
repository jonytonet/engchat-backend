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
        Schema::create('agent_availability', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('users')->onDelete('cascade');

            // Status atual
            $table->enum('current_status', ['online', 'offline', 'busy', 'away', 'break'])->default('offline');
            $table->enum('previous_status', ['online', 'offline', 'busy', 'away', 'break'])->nullable();

            // Capacidade atual
            $table->integer('max_conversations')->default(5);
            $table->integer('current_conversations_count')->default(0);
            $table->integer('available_slots')->storedAs('max_conversations - current_conversations_count');

            // Departamentos disponíveis
            $table->json('available_departments')->nullable()->comment('IDs dos departamentos que pode atender');
            $table->json('preferred_categories')->nullable()->comment('Categorias de preferência');

            // Controle de pausas
            $table->string('break_reason')->nullable();
            $table->timestamp('break_start_time')->nullable();
            $table->timestamp('estimated_return_time')->nullable();

            // Auto-assignment
            $table->boolean('auto_accept_conversations')->default(true);
            $table->boolean('accept_transfers')->default(true);

            // Timestamps importantes
            $table->timestamp('last_status_change')->useCurrent();
            $table->timestamp('last_activity')->useCurrent();
            $table->timestamp('shift_start_time')->nullable();
            $table->timestamp('shift_end_time')->nullable();

            $table->timestamps();

            // Indexes
            $table->unique('agent_id', 'unique_agent_availability');
            $table->index('current_status');
            $table->index('available_slots');
            $table->index('auto_accept_conversations');
            $table->index('last_activity');
            $table->index('last_status_change');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agent_availability');
    }
};
