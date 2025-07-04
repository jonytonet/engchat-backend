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
        Schema::create('contacts', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->nullable()->unique();
            $table->string('phone')->nullable();
            $table->string('display_name')->nullable();
            $table->string('company')->nullable();
            $table->string('document')->nullable();
            $table->json('tags')->nullable();
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium');
            $table->boolean('blacklisted')->default(false);
            $table->text('blacklist_reason')->nullable();
            $table->string('preferred_language', 10)->default('pt-BR');
            $table->string('timezone', 50)->default('America/Sao_Paulo');
            $table->timestamp('last_interaction')->nullable();
            $table->integer('total_interactions')->default(0);
            $table->timestamps();
            $table->softDeletes();

            // Indexes for performance
            $table->index('email');
            $table->index('phone');
            $table->index('document');
            $table->index('priority');
            $table->index('blacklisted');
            $table->index('last_interaction');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
