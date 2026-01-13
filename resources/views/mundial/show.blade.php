<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $partido->equipo_local }} vs {{ $partido->equipo_visitante }} | Mundial 2026</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-slate-900 text-white min-h-screen font-sans">

    {{-- HEADER FIJO (STICKY) --}}
    <div class="bg-slate-900/90 backdrop-blur-md border-b border-white/10 p-4 shadow-2xl sticky top-0 z-50">
        <div class="max-w-4xl mx-auto">
            <a href="{{ route('inicio') }}" class="text-gray-400 hover:text-white mb-2 inline-flex items-center gap-2 text-sm font-bold transition-colors">
                <i class="fa-solid fa-arrow-left"></i> Volver al tablero
            </a>

            <div class="flex justify-between items-center mt-2">

                {{-- Equipo Local --}}
                <div class="text-center w-1/3">
                    <h2 class="text-xl md:text-3xl font-bold truncate">{{ $partido->equipo_local }}</h2>
                </div>

                {{-- Marcador Central y Estado --}}
                <div class="flex flex-col items-center w-1/3">
                    <div class="bg-black/40 px-6 py-2 rounded-xl border border-white/10 backdrop-blur-sm mb-1">
                        <span class="text-4xl md:text-5xl font-mono font-bold text-yellow-400 tracking-widest">
                            {{ $partido->goles_local }}-{{ $partido->goles_visitante }}
                        </span>
                    </div>

                    {{-- LOGICA PHP PARA EL ESTADO (CORRECCIÓN APLICADA AQUÍ) --}}
                    @php
                        $estadoConfig = match($partido->estado) {
                            'en_vivo'     => [
                                'texto' => 'EN VIVO',
                                'clase' => 'bg-red-600/20 text-red-500 border-red-600/50 animate-pulse',
                                'dot'   => 'bg-red-500'
                            ],
                            'entretiempo' => [
                                'texto' => 'ENTRETIEMPO',
                                'clase' => 'bg-orange-600/20 text-orange-500 border-orange-600/50',
                                'dot'   => 'bg-orange-500'
                            ],
                            'finalizado'  => [
                                'texto' => 'FINALIZADO',
                                'clase' => 'bg-gray-600/20 text-gray-400 border-gray-600/50',
                                'dot'   => 'bg-gray-500'
                            ],
                            default       => [
                                'texto' => 'PROGRAMADO',
                                'clase' => 'bg-blue-600/20 text-blue-500 border-blue-600/50',
                                'dot'   => 'bg-blue-500'
                            ]
                        };
                    @endphp

                    {{-- Badge Dinámico --}}
                    <span class="inline-flex items-center gap-2 px-3 py-0.5 rounded-full border text-[10px] font-bold uppercase tracking-wider {{ $estadoConfig['clase'] }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $estadoConfig['dot'] }}"></span>
                        {{ $estadoConfig['texto'] }}
                    </span>
                </div>

                {{-- Equipo Visitante --}}
                <div class="text-center w-1/3">
                    <h2 class="text-xl md:text-3xl font-bold truncate">{{ $partido->equipo_visitante }}</h2>
                </div>
            </div>
        </div>
    </div>

    {{-- CONTENIDO PRINCIPAL --}}
    <div class="max-w-2xl mx-auto mt-8 px-4 pb-20">

        <div class="flex items-center justify-between mb-6 border-b border-gray-700 pb-2">
            <h3 class="text-gray-400 font-bold uppercase text-sm tracking-wider">Minuto a Minuto</h3>
            <span class="text-xs text-gray-500"><i class="fa-regular fa-clock"></i> Actualizando...</span>
        </div>

        <div class="space-y-4 relative">
            {{-- Línea de tiempo vertical decorativa --}}
            <div class="absolute left-6 top-4 bottom-4 w-0.5 bg-gray-800 -z-10"></div>

            {{-- Iteramos los eventos ordenados por minuto DESCENDENTE --}}
            @forelse($partido->eventos->sortByDesc('minuto') as $evento)
                @php
                    $estilos = match($evento->tipo) {
                        'GOL'     => ['border' => 'border-green-500',  'icon' => 'fa-futbol',           'bg' => 'bg-green-900/10',  'text' => 'text-green-400'],
                        'TARJETA' => ['border' => 'border-yellow-500', 'icon' => 'fa-square',           'bg' => 'bg-yellow-900/10', 'text' => 'text-yellow-400'],
                        'ROJA'    => ['border' => 'border-red-500',    'icon' => 'fa-square text-red-600', 'bg' => 'bg-red-900/10',    'text' => 'text-red-400'],
                        'CAMBIO'  => ['border' => 'border-blue-500',   'icon' => 'fa-rotate',           'bg' => 'bg-blue-900/10',   'text' => 'text-blue-400'],
                        default   => ['border' => 'border-gray-600',   'icon' => 'fa-note-sticky',      'bg' => 'bg-gray-800/40',   'text' => 'text-gray-300'],
                    };
                @endphp

                <div class="flex gap-4 {{ $estilos['bg'] }} p-4 rounded-xl border-l-4 {{ $estilos['border'] }} shadow-lg transition-all hover:scale-[1.01] hover:bg-gray-800">

                    {{-- Minuto --}}
                    <div class="flex-shrink-0 w-12 text-center flex flex-col justify-center">
                        <span class="block text-xs text-gray-500 font-mono uppercase">Min</span>
                        <span class="block text-xl font-bold text-white">{{ $evento->minuto }}'</span>
                    </div>

                    {{-- Icono y Descripción --}}
                    <div class="flex-grow">
                        <h4 class="font-bold text-sm {{ $estilos['text'] }} flex items-center gap-2 mb-1">
                            <i class="fa-solid {{ $estilos['icon'] }}"></i>
                            {{ str_replace('_', ' ', $evento->tipo) }}
                        </h4>
                        <p class="text-gray-300 text-sm leading-snug">{{ $evento->descripcion }}</p>
                    </div>
                </div>
            @empty
                <div class="text-center py-12 bg-gray-800/30 rounded-xl border border-dashed border-gray-700">
                    <i class="fa-regular fa-hourglass-half text-3xl text-gray-600 mb-3 animate-pulse"></i>
                    <p class="text-gray-500 italic">El partido está por comenzar o no hay incidencias aún.</p>
                </div>
            @endforelse
        </div>
    </div>

    {{-- SCRIPT DE RECARGA INTELIGENTE --}}
    <script>
        // Recarga cada 5 segundos manteniendo el scroll
        setTimeout(function() {
            sessionStorage.setItem('scrollPos', window.scrollY);
            window.location.reload();
        }, 5000);

        document.addEventListener("DOMContentLoaded", function() {
            var scrollPos = sessionStorage.getItem('scrollPos');
            if (scrollPos) {
                window.scrollTo(0, scrollPos);
                sessionStorage.removeItem('scrollPos');
            }
        });
    </script>

</body>
</html>
