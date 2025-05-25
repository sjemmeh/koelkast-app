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
        Schema::table('Transacties', function (Blueprint $table) {
            $table->foreign(['drank_id'], 'fk_transacties_dranken')->references(['drank_id'])->on('Dranken')->onUpdate('cascade')->onDelete('set null');
            $table->foreign(['persoon_id'], 'fk_transacties_personen')->references(['persoon_id'])->on('Personen')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Transacties', function (Blueprint $table) {
            $table->dropForeign('fk_transacties_dranken');
            $table->dropForeign('fk_transacties_personen');
        });
    }
};
