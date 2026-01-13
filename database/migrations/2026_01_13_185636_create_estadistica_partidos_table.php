<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
{
    Schema::create('estadisticas_partidos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('partido_id')->constrained('partidos');
        // Estadísticas acumuladas
        $table->integer('tiros_arco_local')->default(0);
        $table->integer('tiros_arco_visitante')->default(0);
        $table->integer('posesion_local')->default(50); // Porcentaje
        $table->integer('faltas_local')->default(0);
        $table->integer('faltas_visitante')->default(0);
        // Análisis
        $table->text('momento_destacado')->nullable(); // Ej: "Gol en el minuto 90"
        $table->timestamps();
    });
}
};
