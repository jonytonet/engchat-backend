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
        Schema::create('channels', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->enum('type', ['whatsapp', 'telegram', 'web', 'facebook', 'instagram']);
            $table->json('configuration')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('priority')->default(1);
            $table->json('working_hours')->nullable();
            $table->boolean('auto_response_enabled')->default(true);
            $table->timestamps();

            // Indexes
            $table->index('type');
            $table->index('is_active');
            $table->index('priority');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('channels');
    }
};
