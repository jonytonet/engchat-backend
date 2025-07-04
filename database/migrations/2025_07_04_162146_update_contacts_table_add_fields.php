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
        Schema::table('contacts', function (Blueprint $table) {
            // Company and document info
            $table->string('company')->nullable()->after('display_name');
            $table->string('document', 50)->nullable()->after('company');

            // Tags and priority
            $table->json('tags')->nullable()->after('document');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('medium')->after('tags');

            // Blacklist management
            $table->boolean('blacklisted')->default(false)->after('priority');
            $table->text('blacklist_reason')->nullable()->after('blacklisted');

            // Localization
            $table->string('preferred_language', 10)->default('pt-BR')->after('blacklist_reason');
            $table->string('timezone', 50)->default('America/Sao_Paulo')->after('preferred_language');

            // Interaction tracking
            $table->timestamp('last_interaction')->nullable()->after('timezone');
            $table->integer('total_interactions')->default(0)->after('last_interaction');

            // Indexes
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
        Schema::table('contacts', function (Blueprint $table) {
            // Drop indexes first
            $table->dropIndex(['priority']);
            $table->dropIndex(['blacklisted']);
            $table->dropIndex(['last_interaction']);

            // Drop columns
            $table->dropColumn([
                'company',
                'document',
                'tags',
                'priority',
                'blacklisted',
                'blacklist_reason',
                'preferred_language',
                'timezone',
                'last_interaction',
                'total_interactions'
            ]);
        });
    }
};
