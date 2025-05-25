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
        Schema::create('Personen', function (Blueprint $table) {
            $table->integer('persoon_id', true);
            $table->string('naam');
            $table->boolean('actief')->default(true);
            $table->timestamp('aangemaakt_op')->useCurrent();
            $table->timestamp('gewijzigd_op')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('Personen');
    }
};
