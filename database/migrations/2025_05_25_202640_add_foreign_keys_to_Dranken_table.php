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
        Schema::table('Dranken', function (Blueprint $table) {
            $table->foreign(['categorie_id'], 'fk_dranken_categorieen')->references(['categorie_id'])->on('Categorieen')->onUpdate('cascade')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('Dranken', function (Blueprint $table) {
            $table->dropForeign('fk_dranken_categorieen');
        });
    }
};
