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
        .animate-pulse-slow {
            animation: pulse 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>
<body class="bg-[#0f1115] text-white min-h-screen">

    {{-- NAV SUPERIOR --}}
    <nav class="absolute top-0 w-full z-50 px-6 py-4 flex justify-between items-center">
        <div class="flex items-center gap-2">
            <i class="fa-solid fa-play text-red-600 text-2xl"></i>
            <span class="text-xl font-bold tracking-tighter">FUTBOL<span class="text-red-600">PLAY</span></span>
        </div>
        <div>
            {{-- CONEXIÓN 1: Enlace al panel de administración/árbitro --}}
            <a href="{{ route('admin.index') }}" class="bg-white/10 hover:bg-white/20 px-4 py-2 rounded text-sm font-semibold transition backdrop-blur-sm border border-white/10">
                <i class="fa-solid fa-whistle mr-2"></i> Modo Árbitro
            </a>
        </div>
    </nav>

    @php
        // LOGICA DE VISUALIZACIÓN
        // Separamos el primer partido para el "Hero" y el resto para la grilla
        $destacado = $partidos->first();
        $restoPartidos = $partidos->skip(1);
    @endphp

    {{-- SECCIÓN HERO (PARTIDO DESTACADO) --}}
    @if($destacado)
        <div class="relative w-full h-[85vh] flex items-end pb-20 group">
            {{-- Imagen de fondo --}}
            <img src="https://images.unsplash.com/photo-1551958219-acbc608c6377?q=80&w=2940&auto=format&fit=crop"
                 class="absolute inset-0 w-full h-full object-cover z-0 opacity-60 group-hover:scale-105 transition duration-1000" alt="Fondo Estadio">

            <div class="absolute inset-0 hero-gradient z-10"></div>

            <div class="relative z-20 max-w-7xl mx-auto px-6 w-full">

                <div class="flex items-center gap-3 mb-4">
                    <span class="bg-red-600 text-white text-xs font-bold px-3 py-1 rounded uppercase tracking-wider inline-block">
                        Partido Destacado
                    </span>
                    @if($destacado->estado == 'en_curso')
                        <span class="flex h-3 w-3 relative">
                          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                          <span class="relative inline-flex rounded-full h-3 w-3 bg-green-500"></span>
                        </span>
                        <span class="text-green-400 text-xs font-bold tracking-widest uppercase animate-pulse">En Vivo</span>
                    @endif
                </div>

                <h1 class="text-5xl md:text-7xl font-black mb-2 leading-tight">
                    {{ $destacado->equipo_local }}
                    <span class="text-gray-500 font-thin text-4xl align-middle mx-2">vs</span>
                    {{ $destacado->equipo_visitante }}
                </h1>

                <p class="text-gray-300 text-lg max-w-2xl mb-8">
                    Sigue el minuto a minuto, estadísticas en tiempo real y todos los eventos del partido gracias a nuestra tecnología RabbitMQ.
                </p>

                <div class="flex items-center gap-4">
                    {{-- CONEXIÓN 2: Enlace al Show del partido --}}
                    <a href="{{ route('partido.show', $destacado->id) }}" class="bg-white text-black px-8 py-3 rounded font-bold hover:bg-gray-200 transition flex items-center gap-2 transform hover:scale-105">
                        <i class="fa-solid fa-play"></i> IR A LA TRANSMISIÓN
                    </a>

                    <div class="bg-black/50 backdrop-blur px-6 py-3 rounded text-white font-mono text-2xl font-bold border border-white/10">
                        {{ $destacado->goles_local }} - {{ $destacado->goles_visitante }}
                    </div>
                </div>
            </div>
        </div>
    @else
        {{-- Pantalla Empty State (Si no hay partidos en la BD) --}}
        <div class="h-screen flex items-center justify-center relative z-20 bg-[#0f1115]">
            <div class="text-center">
                <i class="fa-regular fa-calendar-xmark text-6xl text-gray-700 mb-4"></i>
                <h1 class="text-3xl font-bold text-gray-500">No hay partidos programados</h1>
                <a href="{{ route('admin.index') }}" class="text-red-500 hover:text-red-400 underline mt-4 block">Crear un partido</a>
            </div>
        </div>
    @endif

    {{-- GRILLA DE OTROS PARTIDOS --}}
    @if($restoPartidos->count() > 0)
    <div class="max-w-7xl mx-auto px-6 py-10">
        <div class="flex items-center gap-2 mb-6 border-l-4 border-red-600 pl-4">
            <h3 class="text-xl font-bold">Más Encuentros</h3>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

            @foreach($restoPartidos as $partido)
                {{-- CONEXIÓN 3: Enlace de las tarjetas --}}
                <a href="{{ route('partido.show', $partido->id) }}" class="group block h-full">
                    <div class="bg-[#161a23] rounded-xl p-6 border border-white/5 hover:border-red-600/30 transition-all hover:-translate-y-1 hover:shadow-2xl hover:shadow-red-900/10 relative overflow-hidden h-full flex flex-col justify-between">

                        {{-- Badge de Estado --}}
                        <div class="absolute top-4 left-4 z-10">
                            @php
                                $clasesEstado = match($partido->estado) {
                                    'en_curso' => 'text-green-400 border-green-500/30 bg-green-900/30 animate-pulse',
                                    'entretiempo' => 'text-yellow-400 border-yellow-500/30 bg-yellow-900/30',
                                    'finalizado' => 'text-gray-400 border-gray-500/30 bg-gray-800/50',
                                    default => 'text-blue-300 border-blue-500/30 bg-blue-900/50' // Programado
                                };
                                $textoEstado = match($partido->estado) {
                                    'en_curso' => '● EN VIVO',
                                    default => str_replace('_', ' ', strtoupper($partido->estado))
                                };
                            @endphp
                            <span class="{{ $clasesEstado }} text-[10px] font-bold px-2 py-1 rounded border uppercase tracking-wider">
                                {{ $textoEstado }}
                            </span>
                        </div>

                        <div class="mt-8 mb-4">
                            <div class="flex justify-between items-center">
                                {{-- Local --}}
                                <div class="text-center w-1/3">
                                    <div class="w-14 h-14 bg-gradient-to-br from-gray-700 to-gray-800 rounded-full mx-auto mb-2 flex items-center justify-center text-sm font-bold border border-gray-600 shadow-lg">
                                         {{ substr($partido->equipo_local, 0, 3) }}
                                    </div>
                                    <span class="text-sm font-bold block truncate text-gray-300 group-hover:text-white transition">
                                        {{ $partido->equipo_local }}
                                    </span>
                                </div>

                                {{-- Score --}}
                                <div class="text-center w-1/3 flex flex-col items-center">
                                    <span class="text-2xl font-mono font-black tracking-widest text-white group-hover:scale-110 transition duration-300">
                                        {{ $partido->goles_local }}-{{ $partido->goles_visitante }}
                                    </span>
                                </div>

                                {{-- Visitante --}}
                                <div class="text-center w-1/3">
                                    <div class="w-14 h-14 bg-gradient-to-br from-gray-700 to-gray-800 rounded-full mx-auto mb-2 flex items-center justify-center text-sm font-bold border border-gray-600 shadow-lg">
                                        {{ substr($partido->equipo_visitante, 0, 3) }}
                                    </div>
                                    <span class="text-sm font-bold block truncate text-gray-300 group-hover:text-white transition">
                                        {{ $partido->equipo_visitante }}
                                    </span>
                                </div>
                            </div>
                        </div>

                        <div class="border-t border-white/5 pt-3 flex justify-between items-center mt-auto">
                            <span class="text-xs text-gray-500 font-medium group-hover:text-red-500 transition">Ver detalles del partido &rarr;</span>
                        </div>

                    </div>
                </a>
            @endforeach

        </div>
    </div>
    @endif

</body>
</html>
