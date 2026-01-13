<?php

namespace App\Services;

use App\Models\EstadisticaPartido;
use App\Models\Partido;

class StatsService
{
    public function registrarEvento(int $partidoId, string $tipoEvento, string $equipo)
    {
        // 1. Busca o crea la estadística para este partido
        $stats = EstadisticaPartido::firstOrCreate(['partido_id' => $partidoId]);

        // 2. Analiza qué pasó y actualiza
        if ($tipoEvento === 'gol') {
            // Aquí podrías calcular cosas complejas, como la frecuencia de goles
            $this->analizarImpactoGol($stats, $equipo);
        }

        if ($tipoEvento === 'tiro_arco') {
            if ($equipo === 'local') {
                $stats->increment('tiros_arco_local');
            } else {
                $stats->increment('tiros_arco_visitante');
            }
        }

        // 3. Guardar
        $stats->save();

        return $stats;
    }

    private function analizarImpactoGol($stats, $equipo)
    {
        // Lógica de "Análisis": Generar un comentario automático
        $stats->momento_destacado = "El equipo $equipo cambió el ritmo del partido con un gol clave.";
    }
}
