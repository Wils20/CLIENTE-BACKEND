<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Partido;

class PartidoController extends Controller
{
    /**
     * Muestra la PORTADA PÚBLICA (Landing Page).
     * Envía la lista de partidos para el Hero y la Grilla.
     */
    public function index()
    {
        // TRUCO SQL: Usamos orderByRaw para dar prioridad personalizada a los estados.
        // 1. En Curso (Para que salga gigante en el banner)
        // 2. Entretiempo
        // 3. Programado
        // 4. Finalizado (Al final)
        $partidos = Partido::orderByRaw("FIELD(estado, 'en_curso', 'entretiempo', 'PROGRAMADO', 'finalizado')")
                           ->orderBy('id', 'desc') // Si empatan en estado, el más nuevo primero
                           ->get();

        return view('welcome', compact('partidos'));
    }

    /**
     * Muestra la VISTA CLIENTE (Estadísticas en vivo).
     * Recibe el ID, carga los eventos y muestra el marcador.
     */
    public function show($id)
    {
        // Buscamos el partido. Si no existe, lanza error 404.
        // Usamos 'with' para traer los eventos ordenados del más reciente al más antiguo.
        $partido = Partido::with(['eventos' => function($query) {
            $query->orderBy('id', 'desc');
        }])->findOrFail($id);

        return view('partido.show', compact('partido'));
    }

    /**
     * Muestra el DASHBOARD DEL ADMINISTRADOR.
     * Donde se crean los partidos y se eligen para arbitrar.
     */
    public function adminIndex()
    {
        // Aquí mostramos una lista simple para que el admin gestione
        $partidos = Partido::orderBy('id', 'desc')->get();

        // Asegúrate de crear la vista: resources/views/admin/index.blade.php
        return view('admin.index', compact('partidos'));
    }

    /**
     * Guarda un NUEVO PARTIDO en la base de datos.
     */
    public function store(Request $request)
    {
        // 1. Validamos que envíen los nombres de los equipos
        $request->validate([
            'equipo_local' => 'required|string|max:255',
            'equipo_visitante' => 'required|string|max:255',
        ]);

        // 2. Creamos el partido con valores por defecto
        Partido::create([
            'equipo_local' => $request->equipo_local,
            'equipo_visitante' => $request->equipo_visitante,
            'goles_local' => 0,
            'goles_visitante' => 0,
            'estado' => 'PROGRAMADO', // Estado inicial obligatoriamente
            'hora_inicio' => null
        ]);

        // 3. Redirigimos con mensaje de éxito
        return back()->with('success', 'Partido programado correctamente.');
    }
}
