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
        Schema::create('conversation_transfers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('conversation_id')->constrained()->onDelete('cascade');
            $table->foreignId('from_user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('to_user_id')->constrained('users')->onDelete('cascade');

            // Transfer details
            $table->enum('reason', ['workload', 'expertise', 'unavailable', 'other'])->default('other');
            $table->text('notes')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected'])->default('accepted');

            // Timing
            $table->timestamp('transferred_at')->useCurrent();
            $table->timestamp('accepted_at')->nullable();

            $table->timestamps();

            // Indexes
            $table->index('conversation_id');
            $table->index('from_user_id');
            $table->index('to_user_id');
            $table->index('transferred_at');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('conversation_transfers');
    }
};
