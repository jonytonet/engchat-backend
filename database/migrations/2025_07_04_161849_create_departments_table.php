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
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->unsignedBigInteger('manager_id')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('working_hours')->nullable();
            $table->boolean('auto_assignment_enabled')->default(true);
            $table->timestamps();

            // Foreign keys
            $table->foreign('manager_id')->references('id')->on('users')->onDelete('set null');

            // Indexes
            $table->index('manager_id');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};
