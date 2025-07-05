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
        Schema::create('bot_conversations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
            $table->foreignId('contact_id')->constrained()->onDelete('cascade');

            // Estado do bot
            $table->string('current_step', 100)->comment('welcome, collect_name, classify, etc');
            $table->unsignedBigInteger('bot_flow_id')->nullable();

            // Dados coletados pelo bot
            $table->json('collected_data')->nullable()->comment('Dados coletados durante o fluxo');
            $table->json('classification_result')->nullable()->comment('Resultado da classificação automática');
            $table->decimal('confidence_score', 5, 2)->nullable()->comment('Confiança da classificação');

            // Status do handoff
            $table->boolean('requires_human')->default(false);
            $table->string('handoff_reason')->nullable();
            $table->json('attempted_classifications')->nullable()->comment('Tentativas de classificação');

            // Controle de fluxo
            $table->boolean('is_completed')->default(false);
            $table->timestamp('completed_at')->nullable();
            $table->timestamp('escalated_at')->nullable();

            // Metadados
            $table->timestamp('started_at')->useCurrent();
            $table->timestamp('last_interaction_at')->useCurrent();
            $table->timestamps();

            // Indexes
            $table->index('conversation_id');
            $table->index('contact_id');
            $table->index('current_step');
            $table->index('is_completed');
            $table->index('requires_human');
            $table->index('last_interaction_at');

            // Unique constraint para evitar duplicatas
            $table->unique('conversation_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bot_conversations');
    }
};
