<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('partidos', function (Blueprint $table) {
            $table->id();
            $table->string('equipo_local'); // Ej: España
            $table->string('equipo_visita'); // Ej: Brasil
            $table->integer('goles_local')->default(0);
            $table->integer('goles_visita')->default(0);
            $table->string('estado')->default('PROGRAMADO'); // JUGANDO, FINALIZADO
            $table->timestamps();
        });
    }
};
