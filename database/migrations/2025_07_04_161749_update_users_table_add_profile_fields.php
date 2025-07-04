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
        Schema::table('users', function (Blueprint $table) {
            // Avatar/Profile photo support
            $table->string('avatar')->nullable()->after('email');

            // User status and activity
            $table->enum('status', ['online', 'offline', 'busy', 'away'])
                  ->default('offline')
                  ->after('avatar');

            // Foreign keys for role and department (to be created later)
            $table->unsignedBigInteger('role_id')->nullable()->after('status');
            $table->unsignedBigInteger('department_id')->nullable()->after('role_id');
            $table->unsignedBigInteger('manager_id')->nullable()->after('department_id');

            // Activity tracking
            $table->timestamp('last_activity')->nullable()->after('manager_id');

            // Localization
            $table->string('timezone', 50)->default('America/Sao_Paulo')->after('last_activity');
            $table->string('language', 10)->default('pt-BR')->after('timezone');

            // Account status
            $table->boolean('is_active')->default(true)->after('language');

            // Indexes for performance
            $table->index('status');
            $table->index('role_id');
            $table->index('department_id');
            $table->index('last_activity');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['status']);
            $table->dropIndex(['role_id']);
            $table->dropIndex(['department_id']);
            $table->dropIndex(['last_activity']);

            // Drop columns
            $table->dropColumn([
                'avatar',
                'status',
                'role_id',
                'department_id',
                'manager_id',
                'last_activity',
                'timezone',
                'language',
                'is_active'
            ]);
        });
    }
};
