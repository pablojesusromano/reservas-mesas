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
        Schema::create('mesa_reserva', function (Blueprint $table) {
            $table->foreignId('reserva_id')->constrained()->cascadeOnDelete();
            $table->foreignId('mesa_id')->constrained()->cascadeOnDelete();

            $table->primary(['reserva_id', 'mesa_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mesa_reserva');
    }
};
