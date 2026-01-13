<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $partido->equipo_local }} vs {{ $partido->equipo_visitante }} | Mundial 2026</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        .stat-bar { transition: width 1s ease-in-out; }
    </style>
</head>
<body class="bg-slate-900 text-white min-h-screen font-sans">

    {{-- LÓGICA PHP INICIAL --}}
    @php
    // 1. CÁLCULO DE SEGUNDOS A PRUEBA DE ZONAS HORARIAS
    $segundosIniciales = 0;

    if($partido->estado == 'en_curso' && $partido->hora_inicio) {
        // Convertimos ambas fechas a TIMESTAMP (segundos universales desde 1970)
        // Esto ignora si uno es UTC y el otro es Peru, solo compara el instante real.
        $inicio = \Carbon\Carbon::parse($partido->hora_inicio)->timestamp;
        $ahora = now()->timestamp;

        $segundosIniciales = $ahora - $inicio;

        // CORRECCIÓN DE EMERGENCIA (Si sale negativo por configuración del servidor)
        // Si el resultado es negativo (ej: -18000), asumimos que es error de zona horaria (5h) y lo corregimos.
        if ($segundosIniciales < 0) {
            $segundosIniciales = 0;
        }
    }

    // 2. ESTADÍSTICAS (El resto sigue igual)
    $eventosLocal = $partido->eventos->where('equipo', $partido->equipo_local);
    $eventosVisitante = $partido->eventos->where('equipo', $partido->equipo_visitante);

    $stats = [
        'local' => [
            'amarillas' => $eventosLocal->where('tipo', 'TARJETA_AMARILLA')->count(),
            'rojas'     => $eventosLocal->where('tipo', 'TARJETA_ROJA')->count(),
            'cambios'   => $eventosLocal->where('tipo', 'CAMBIO')->count(),
            'actividad' => $eventosLocal->count() + 1,
        ],
        'visitante' => [
            'amarillas' => $eventosVisitante->where('tipo', 'TARJETA_AMARILLA')->count(),
            'rojas'     => $eventosVisitante->where('tipo', 'TARJETA_ROJA')->count(),
            'cambios'   => $eventosVisitante->where('tipo', 'CAMBIO')->count(),
            'actividad' => $eventosVisitante->count() + 1,
        ]
    ];

    $totalActividad = $stats['local']['actividad'] + $stats['visitante']['actividad'];
    $porcentajeLocal = $totalActividad > 0 ? round(($stats['local']['actividad'] / $totalActividad) * 100) : 50;
    $porcentajeVisitante = 100 - $porcentajeLocal;
@endphp

    {{-- HEADER --}}
    <div class="bg-slate-900/95 backdrop-blur-md border-b border-white/10 p-4 shadow-2xl sticky top-0 z-50">
        <div class="max-w-5xl mx-auto">
            <a href="{{ route('inicio') }}" class="text-gray-400 hover:text-white mb-2 inline-flex items-center gap-2 text-sm font-bold transition-colors">
                <i class="fa-solid fa-arrow-left"></i> Volver
            </a>

            <div class="flex justify-between items-start mt-2 relative">

                {{-- Local --}}
                <div class="text-center w-1/3 pt-2">
                    <h2 class="text-xl md:text-3xl font-bold truncate text-blue-400 drop-shadow-md">
                        {{ $partido->equipo_local }}
                    </h2>
                </div>

                {{-- CENTRO: SOLUCIÓN DE SUPERPOSICIÓN --}}
                <div class="flex flex-col items-center w-1/3 z-10">

                    {{-- 1. CAJA DEL MARCADOR --}}
                    <div class="bg-black/60 px-6 py-2 rounded-xl border border-white/10 backdrop-blur-sm shadow-lg mb-2">
                        <span class="text-4xl md:text-6xl font-mono font-bold text-white tracking-widest">
                            {{ $partido->goles_local }}-{{ $partido->goles_visitante }}
                        </span>
                    </div>

                    {{-- 2. CRONÓMETRO (Ya no es absolute, ahora está en el flujo normal) --}}
                    @if($partido->estado === 'en_curso')
                        <div class="mb-2">
                            <span id="reloj-juego" class="bg-black text-green-400 border border-green-500/50 px-3 py-0.5 rounded text-lg font-mono font-bold shadow-lg tracking-widest">
                                00:00
                            </span>
                        </div>
                    @endif

                    {{-- 3. BADGE DE ESTADO --}}
                    @php
                        $estadoConfig = match($partido->estado) {
                            'en_curso'    => ['texto' => 'EN VIVO',     'clase' => 'bg-red-600/20 text-red-500 border-red-600/50 animate-pulse', 'dot' => 'bg-red-500'],
                            'entretiempo' => ['texto' => 'ENTRETIEMPO', 'clase' => 'bg-orange-600/20 text-orange-500 border-orange-600/50',      'dot' => 'bg-orange-500'],
                            'finalizado'  => ['texto' => 'FINALIZADO',  'clase' => 'bg-gray-600/20 text-gray-400 border-gray-600/50',            'dot' => 'bg-gray-500'],
                            default       => ['texto' => 'PROGRAMADO',  'clase' => 'bg-blue-600/20 text-blue-500 border-blue-600/50',            'dot' => 'bg-blue-500']
                        };
                    @endphp

                    <span class="inline-flex items-center gap-2 px-3 py-0.5 rounded-full border text-[10px] font-bold uppercase tracking-wider {{ $estadoConfig['clase'] }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $estadoConfig['dot'] }}"></span>
                        {{ $estadoConfig['texto'] }}
                    </span>
                </div>

                {{-- Visitante --}}
                <div class="text-center w-1/3 pt-2">
                    <h2 class="text-xl md:text-3xl font-bold truncate text-red-400 drop-shadow-md">
                        {{ $partido->equipo_visitante }}
                    </h2>
                </div>
            </div>
        </div>
    </div>

    {{-- CONTENIDO (GRILLA) --}}
    <div class="max-w-6xl mx-auto mt-8 px-4 pb-20 grid grid-cols-1 lg:grid-cols-3 gap-8">
        {{-- COLUMNA IZQUIERDA: ESTADÍSTICAS --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Dominio --}}
            <div class="bg-[#1a1f2e] rounded-xl p-5 border border-white/5 shadow-lg">
                <h3 class="text-gray-400 font-bold uppercase text-xs tracking-wider mb-4 flex items-center gap-2">
                    <i class="fa-solid fa-chart-pie"></i> Tendencia
                </h3>
                <div class="flex justify-between text-sm font-bold mb-1">
                    <span class="text-blue-400">{{ $porcentajeLocal }}%</span>
                    <span class="text-red-400">{{ $porcentajeVisitante }}%</span>
                </div>
                <div class="w-full h-3 bg-gray-700 rounded-full overflow-hidden flex">
                    <div class="h-full bg-blue-600 stat-bar" style="width: {{ $porcentajeLocal }}%"></div>
                    <div class="h-full bg-red-600 stat-bar" style="width: {{ $porcentajeVisitante }}%"></div>
                </div>
            </div>

            {{-- Disciplina --}}
            <div class="bg-[#1a1f2e] rounded-xl p-5 border border-white/5 shadow-lg">
                <h3 class="text-gray-400 font-bold uppercase text-xs tracking-wider mb-4 border-b border-white/5 pb-2">Resumen</h3>

                {{-- Items simplificados --}}
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="font-bold w-8 text-center">{{ $stats['local']['amarillas'] }}</span>
                        <span class="text-xs text-gray-500 uppercase"><i class="fa-solid fa-square text-yellow-400"></i> Amarillas</span>
                        <span class="font-bold w-8 text-center">{{ $stats['visitante']['amarillas'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="font-bold w-8 text-center">{{ $stats['local']['rojas'] }}</span>
                        <span class="text-xs text-gray-500 uppercase"><i class="fa-solid fa-square text-red-600"></i> Rojas</span>
                        <span class="font-bold w-8 text-center">{{ $stats['visitante']['rojas'] }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="font-bold w-8 text-center">{{ $stats['local']['cambios'] }}</span>
                        <span class="text-xs text-gray-500 uppercase"><i class="fa-solid fa-rotate text-blue-400"></i> Cambios</span>
                        <span class="font-bold w-8 text-center">{{ $stats['visitante']['cambios'] }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- COLUMNA DERECHA: MINUTO A MINUTO --}}
        <div class="lg:col-span-2">
            <div class="bg-[#1a1f2e] rounded-xl p-6 border border-white/5 min-h-[500px]">
                <div class="flex items-center justify-between mb-6 border-b border-gray-700 pb-4">
                    <h3 class="text-gray-400 font-bold uppercase text-sm tracking-wider flex items-center gap-2">
                        <i class="fa-solid fa-list-ul"></i> Minuto a Minuto
                    </h3>
                    <span class="text-xs text-green-400 flex items-center gap-1 bg-green-900/20 px-2 py-1 rounded">
                        <span class="w-1.5 h-1.5 bg-green-500 rounded-full animate-pulse"></span> En Línea
                    </span>
                </div>

                <div class="space-y-4 relative pl-2">
                    <div class="absolute left-8 top-4 bottom-4 w-0.5 bg-gray-800 -z-10"></div>

                    @forelse($partido->eventos->sortByDesc('minuto') as $evento)
                        @php
                            $estilos = match($evento->tipo) {
                                'GOL'     => ['border' => 'border-green-500', 'icon' => 'fa-futbol',          'bg' => 'bg-green-900/20', 'text' => 'text-green-400'],
                                'TARJETA' => ['border' => 'border-yellow-500','icon' => 'fa-square',          'bg' => 'bg-yellow-900/20','text' => 'text-yellow-400'],
                                'ROJA'    => ['border' => 'border-red-500',   'icon' => 'fa-square text-red-600','bg' => 'bg-red-900/20', 'text' => 'text-red-400'],
                                'CAMBIO'  => ['border' => 'border-blue-500',  'icon' => 'fa-rotate',          'bg' => 'bg-blue-900/20',  'text' => 'text-blue-400'],
                                'INICIO'  => ['border' => 'border-gray-500',  'icon' => 'fa-whistle',         'bg' => 'bg-gray-700/50',  'text' => 'text-white'],
                                'FIN'     => ['border' => 'border-gray-500',  'icon' => 'fa-flag-checkered',  'bg' => 'bg-gray-700/50',  'text' => 'text-white'],
                                default   => ['border' => 'border-gray-600',  'icon' => 'fa-note-sticky',     'bg' => 'bg-gray-800/40',  'text' => 'text-gray-300'],
                            };
                        @endphp

                        <div class="flex gap-4 {{ $estilos['bg'] }} p-4 rounded-xl border-l-4 {{ $estilos['border'] }} shadow-md items-start">
                            <div class="flex-shrink-0 w-12 text-center pt-1">
                                <span class="block text-[10px] text-gray-500 font-mono uppercase">Min</span>
                                <span class="block text-xl font-black text-white">{{ $evento->minuto }}'</span>
                            </div>
                            <div class="flex-grow">
                                <h4 class="font-bold text-sm {{ $estilos['text'] }} flex items-center gap-2 mb-1 uppercase">
                                    <i class="fa-solid {{ $estilos['icon'] }}"></i> {{ str_replace('_', ' ', $evento->tipo) }}
                                </h4>
                                <p class="text-gray-300 text-sm font-medium">
                                    {{ $evento->descripcion }}
                                    @if($evento->equipo) <span class="text-xs text-gray-500 ml-1">({{ $evento->equipo }})</span> @endif
                                </p>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-10 text-gray-500 italic">Esperando inicio del partido...</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- SCRIPTS JS --}}
    <script>
    // 1. LÓGICA DEL RELOJ
    // Recibimos un ENTERO desde PHP.
    let segundosTranscurridos = parseInt("{{ $segundosIniciales }}");
    const estadoPartido = "{{ $partido->estado }}";
    const relojElement = document.getElementById('reloj-juego');

    function formatearTiempo(segundosTotal) {
        // Aseguramos que sea entero y no negativo
        let s = Math.floor(segundosTotal);
        if (s < 0) s = 0;

        const minutos = Math.floor(s / 60);
        const segundos = s % 60;

        // Formateo con ceros (Ej: 05:04)
        const mStr = minutos.toString().padStart(2, '0');
        const sStr = segundos.toString().padStart(2, '0');

        return `${mStr}:${sStr}`;
    }

    if (estadoPartido === 'en_curso' && relojElement) {
        // Pintamos el tiempo inicial
        relojElement.innerText = formatearTiempo(segundosTranscurridos);

        // Intervalo que suma 1 segundo cada 1000ms
        setInterval(() => {
            segundosTranscurridos++;
            relojElement.innerText = formatearTiempo(segundosTranscurridos);
        }, 1000);
    }

    // 2. RECARGA AUTOMÁTICA
    setTimeout(() => {
        sessionStorage.setItem('scrollPos', window.scrollY);
        window.location.reload();
    }, 5000);

    document.addEventListener("DOMContentLoaded", () => {
        const scrollPos = sessionStorage.getItem('scrollPos');
        if (scrollPos) {
            window.scrollTo(0, scrollPos);
            sessionStorage.removeItem('scrollPos');
        }
    });
</script>

</body>
</html>
