<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('eventos', function (Blueprint $table) {
            $table->id();
            // Esto conecta el evento con el partido. Si borras el partido, se borran sus eventos.
            $table->foreignId('partido_id')->constrained('partidos')->onDelete('cascade');

            $table->string('tipo'); // GOL, TARJETA, INICIO
            $table->string('minuto');
            $table->string('descripcion')->nullable(); // Ej: "Gol de cabeza"
            $table->timestamps();
        });
    }
};
