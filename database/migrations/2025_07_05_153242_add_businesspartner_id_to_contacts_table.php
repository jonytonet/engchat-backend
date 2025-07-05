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
            $table->string('businesspartner_id', 50)->nullable()->after('id')
                ->comment('ID do cliente/parceiro de negócio no sistema ERP externo para integração');

            // Adicionar índice único para garantir integridade na integração
            $table->unique('businesspartner_id', 'idx_contacts_businesspartner_id_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->dropUnique('idx_contacts_businesspartner_id_unique');
            $table->dropColumn('businesspartner_id');
        });
    }
};
