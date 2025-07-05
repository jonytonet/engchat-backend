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
            $table->string('erp_user_id', 50)->nullable()->after('id')
                ->comment('ID do usuário no sistema ERP externo para integração');

            // Adicionar índice para otimizar consultas de integração
            $table->index('erp_user_id', 'idx_users_erp_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex('idx_users_erp_user_id');
            $table->dropColumn('erp_user_id');
        });
    }
};
