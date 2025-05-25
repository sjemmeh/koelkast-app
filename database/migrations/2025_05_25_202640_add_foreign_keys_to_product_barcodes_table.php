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
        Schema::table('product_barcodes', function (Blueprint $table) {
            $table->foreign(['drank_id'])->references(['drank_id'])->on('Dranken')->onUpdate('restrict')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('product_barcodes', function (Blueprint $table) {
            $table->dropForeign('product_barcodes_drank_id_foreign');
        });
    }
};
