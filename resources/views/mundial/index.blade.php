<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>FutbolPlay | Mundial 2026</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <style>
        body { font-family: 'Inter', sans-serif; }
        .hero-gradient {
            background: linear-gradient(to top, #0f1115 10%, rgba(15, 17, 21, 0.8) 50%, rgba(15, 17, 21, 0.4) 100%);
        }
    </style>
</head>
<body class="bg-[#0f1115] text-white min-h-screen">

    <nav class="absolute top-0 w-full z-50 px-6 py-4 flex justify-between items-center">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-play text-red-600 text-2xl"></i>
            <span class="text-xl font-bold tracking-tighter">FUTBOL<span class="text-red-600">PLAY</span></span>
        </div>
        <div>
            <button class="bg-white/10 hover:bg-white/20 px-4 py-2 rounded text-sm font-semibold transition backdrop-blur-sm">
                Modo Árbitro
            </button>
        </div>
    </nav>

    @php
        // CORRECCIÓN 1: Asignamos correctamente el primer partido a la variable $destacado
        // Aseguramos que $partidos exista para evitar errores
        $destacado = $partidos->first();

        // El resto los guardamos para la grilla de abajo
        $restoPartidos = $partidos->skip(1);
    @endphp

    @if($destacado)
        <div class="relative w-full h-[85vh] flex items-end pb-20">
            {{-- Imagen de fondo --}}
            <img src="https://images.unsplash.com/photo-1551958219-acbc608c6377?q=80&w=2940&auto=format&fit=crop"
                 class="absolute inset-0 w-full h-full object-cover z-0" alt="Fondo Estadio">

            <div class="absolute inset-0 hero-gradient z-10"></div>

            <div class="relative z-20 max-w-7xl mx-auto px-6 w-full">

                <span class="bg-red-600 text-white text-xs font-bold px-3 py-1 rounded uppercase tracking-wider mb-4 inline-block">
                    Partido Destacado
                </span>

                {{-- CORRECCIÓN 2: Aquí tenías 'goles_visitante' en el título. Lo cambié a 'equipo_visitante' --}}
                <h1 class="text-5xl md:text-7xl font-black mb-2 leading-tight">
                    {{ $destacado->equipo_local }}
                    <span class="text-gray-400 font-light text-4xl align-middle mx-2">vs</span>
                    {{ $destacado->equipo_visitante }}
                </h1>

                <p class="text-gray-300 text-lg max-w-2xl mb-8">
                    Transmisión exclusiva desde el estadio. Cobertura completa con seguimiento en tiempo real vía RabbitMQ.
                </p>

                <div class="flex items-center gap-4">
                    <a href="{{ route('partido.show', $destacado->id) }}" class="bg-white text-black px-8 py-3 rounded font-bold hover:bg-gray-200 transition flex items-center gap-2">
                        <i class="fa-solid fa-play"></i> VER TRANSMISIÓN
                    </a>
                    <div class="bg-black/50 backdrop-blur px-4 py-3 rounded text-white font-mono font-bold border border-white/10">
                        {{-- CORRECCIÓN 3: Unificamos variables a 'goles_visitante' --}}
                        {{ $destacado->goles_local }} - {{ $destacado->goles_visitante }}
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- Pantalla cuando no hay ningún partido --}}
        <div class="h-screen flex items-center justify-center relative z-20">
            <div class="text-center">
                <i class="fa-regular fa-calendar-xmark text-6xl text-gray-600 mb-4"></i>
                <h1 class="text-3xl font-bold text-gray-500">No hay partidos programados</h1>
            </div>
        </div>
    @endif

    <div class="max-w-7xl mx-auto px-6 py-10">
        <div class="flex items-center gap-2 mb-6 border-l-4 border-red-600 pl-4">
            <h3 class="text-xl font-bold">Cartelera de Hoy</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            @forelse($restoPartidos as $partido)
                <a href="{{ route('partido.show', $partido->id) }}" class="group block">
                    <div class="bg-[#161a23] rounded-xl p-6 border border-white/5 hover:border-white/20 transition-all hover:-translate-y-1 hover:shadow-xl relative overflow-hidden">

                        <div class="absolute top-4 left-4">
                            {{-- Lógica de colores para el estado --}}
                            @php
                                $estadoColor = match($partido->estado) {
                                    'en_vivo' => 'text-green-300 border-green-500/30 bg-green-900/50',
                                    'finalizado' => 'text-gray-400 border-gray-500/30 bg-gray-800/50',
                                    default => 'text-blue-300 border-blue-500/30 bg-blue-900/50'
                                };
                            @endphp
                            <span class="{{ $estadoColor }} text-[10px] font-bold px-2 py-1 rounded border uppercase">
                                {{ str_replace('_', ' ', $partido->estado) }}
                            </span>
                        </div>

                        <div class="flex justify-between items-center mt-6 mb-4">
                            {{-- Equipo Local --}}
                            <div class="text-center w-1/3">
                                <div class="w-12 h-12 bg-gray-700 rounded-full mx-auto mb-2 flex items-center justify-center text-sm font-bold border border-gray-600 overflow-hidden">
                                     {{-- Fallback si no hay logo --}}
                                    {{ substr($partido->equipo_local, 0, 3) }}
                                </div>
                                <span class="text-sm font-bold block truncate" title="{{ $partido->equipo_local }}">
                                    {{ $partido->equipo_local }}
                                </span>
                            </div>

                            {{-- Marcador --}}
                            <div class="text-center w-1/3">
                                <span class="text-xs text-gray-500 block mb-1">VS</span>
                                <span class="text-xl font-mono font-bold tracking-widest text-white">
                                    {{ $partido->goles_local }} : {{ $partido->goles_visitante }}
                                </span>
                            </div>

                            {{-- Equipo Visitante --}}
                            <div class="text-center w-1/3">
                                <div class="w-12 h-12 bg-gray-700 rounded-full mx-auto mb-2 flex items-center justify-center text-sm font-bold border border-gray-600 overflow-hidden">
                                    {{ substr($partido->equipo_visitante, 0, 3) }}
                                </div>
                                <span class="text-sm font-bold block truncate" title="{{ $partido->equipo_visitante }}">
                                    {{ $partido->equipo_visitante }}
                                </span>
                            </div>
                        </div>

                        <div class="border-t border-white/5 pt-3 mt-2 flex justify-between items-center">
                            <span class="text-xs text-red-500 font-bold uppercase tracking-wide">Mundial 2026</span>
                            <span class="text-xs text-gray-400">ID: {{ $partido->id }}</span>
                        </div>

                        {{-- Efecto Hover Brillo --}}
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/5 to-transparent -translate-x-full group-hover:translate-x-full transition-transform duration-1000"></div>
                    </div>
                </a>
            @empty
                {{-- Solo mostrar mensaje si no hay destacado y tampoco resto de partidos --}}
                @if(!$destacado)
                    <div class="col-span-3 text-center py-10 text-gray-500">
                        Esperando señal del servidor...
                    </div>
                @endif
            @endforelse

        </div>
    </div>

</body>
</html>
