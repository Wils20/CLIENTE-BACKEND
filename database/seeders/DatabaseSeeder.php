<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Partido; // <--- Importante importar el modelo

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Aquí es donde defines "Argentina" y "Francia"
        Partido::create([
            'equipo_local' => 'Argentina',
            'equipo_visita' => 'Francia',
            'goles_local' => 0,
            'goles_visita' => 0,
            'estado' => 'EN VIVO'
        ]);

        // Puedes agregar más partidos falsos si quieres ver la grilla llena
        Partido::create([
            'equipo_local' => 'Brasil',
            'equipo_visita' => 'España',
            'goles_local' => 1,
            'goles_visita' => 1,
            'estado' => 'JUGANDO'
        ]);
    }
}
