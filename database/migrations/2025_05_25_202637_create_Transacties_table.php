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
        Schema::create('Transacties', function (Blueprint $table) {
            $table->integer('transactie_id', true);
            $table->integer('persoon_id')->index('fk_transacties_personen');
            $table->unsignedInteger('drank_id')->nullable()->index('fk_transacties_dranken');
            $table->string('onbekende_barcode')->nullable();
            $table->string('omschrijving_ten_tijde_van_transactie');
            $table->decimal('prijs_ten_tijde_van_transactie', 10);
            $table->timestamp('transactie_datum_tijd')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Transacties');
    }
};
