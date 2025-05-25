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
        Schema::create('Dranken', function (Blueprint $table) {
            $table->increments('drank_id');
            $table->string('barcode')->nullable()->unique('barcode');
            $table->string('naam_drank');
            $table->integer('categorie_id')->index('fk_dranken_categorieen');
            $table->decimal('prijs', 10);
            $table->date('tht_datum')->nullable();
            $table->timestamp('toegevoegd_op')->useCurrent();
            $table->timestamp('gewijzigd_op')->useCurrentOnUpdate()->useCurrent();
            $table->boolean('is_actief')->default(true);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Dranken');
    }
};
