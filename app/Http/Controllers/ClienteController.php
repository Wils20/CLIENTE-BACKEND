<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Partido;

class ClienteController extends Controller
{
    // Vista Principal: Lista de todos los partidos
    public function index()
    {
        // Traemos todos los partidos de la BD
        $partidos = Partido::all();
        return view('mundial.index', compact('partidos'));
    }

    // Vista Detalle: Marcador y Eventos de un solo partido
    public function show($id)
    {
        // Buscamos el partido y cargamos sus eventos ordenados por minuto (descendente)
        $partido = Partido::with(['eventos' => function($query) {
            $query->orderBy('created_at', 'desc');
        }])->findOrFail($id);

        return view('mundial.show', compact('partido'));
    }
}
