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
            // Adicionar campos que podem estar faltando na tabela de contatos
            if (!Schema::hasColumn('contacts', 'avatar')) {
                $table->string('avatar')->nullable()->after('phone');
            }
            
            if (!Schema::hasColumn('contacts', 'tags')) {
                $table->json('tags')->nullable()->after('avatar');
            }
            
            if (!Schema::hasColumn('contacts', 'metadata')) {
                $table->json('metadata')->nullable()->after('tags');
            }
            
            if (!Schema::hasColumn('contacts', 'blocked_at')) {
                $table->timestamp('blocked_at')->nullable()->after('metadata');
            }
            
            if (!Schema::hasColumn('contacts', 'last_interaction_at')) {
                $table->timestamp('last_interaction_at')->nullable()->after('blocked_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn([
                'avatar',
                'tags', 
                'metadata',
                'blocked_at',
                'last_interaction_at'
            ]);
        });
    }
};
