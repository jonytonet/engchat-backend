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
        Schema::create('message_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('display_name', 150);
            $table->string('template_code', 100)->unique();
            $table->string('language', 10)->default('pt_BR');
            $table->enum('category', ['utility', 'marketing', 'authentication'])->default('utility');
            $table->enum('approval_status', ['pending', 'approved', 'rejected'])->default('pending');

            // Conteúdo do template
            $table->text('body_content');
            $table->text('header_content')->nullable();
            $table->text('footer_content')->nullable();

            // Parâmetros e variáveis
            $table->json('parameters')->nullable()->comment('Parâmetros dinâmicos do template');
            $table->integer('variables_count')->default(0);

            // Configurações
            $table->boolean('is_global')->default(false);
            $table->boolean('is_active')->default(true);

            // Metadados
            $table->foreignId('created_by')->constrained('users')->onDelete('cascade');
            $table->timestamps();

            // Indexes
            $table->index('template_code');
            $table->index('language');
            $table->index('category');
            $table->index('approval_status');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('message_templates');
    }
};
