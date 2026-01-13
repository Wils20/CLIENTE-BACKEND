<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use App\Models\Partido;
use App\Models\Evento;

class ConsumirMundial extends Command
{
    // Este es el comando que escribirás en la terminal
    protected $signature = 'mundial:escuchar';
    protected $description = 'Escucha eventos de RabbitMQ y actualiza MySQL';

    public function handle()
    {
        $this->info("🏆 SISTEMA CLIENTE INICIADO");
        $this->info("   Esperando datos del árbitro... (Presiona Ctrl+C para detener)");

        // 1. Conexión a RabbitMQ
        $connection = new AMQPStreamConnection('localhost', 5672, 'guest', 'guest');
        $channel = $connection->channel();

        // 2. Declaramos el Exchange (Mismo nombre que en el Árbitro)
        $channel->exchange_declare('mundial_exchange', 'topic', false, true, false);

        // 3. Creamos una cola temporal y exclusiva
        list($queue_name, ,) = $channel->queue_declare("", false, false, true, false);

        // 4. Unimos la cola al canal. '#' significa que escuchamos TODOS los partidos
        $channel->queue_bind($queue_name, 'mundial_exchange', 'partido.#');

        // 5. Lógica cuando llega un mensaje
        $callback = function ($msg) {
            $this->info('📩 Mensaje recibido, procesando...');

            $data = json_decode($msg->body, true);
            $this->procesarDatos($data);
        };

        // 6. Consumir
        $channel->basic_consume($queue_name, '', false, true, false, false, $callback);

        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }

    private function procesarDatos($data)
    {
        // A. BUSCAR O CREAR PARTIDO
        // Si el partido ID 1 no existe en MySQL, lo crea al instante.
        $partido = Partido::firstOrCreate(
            ['id' => $data['partido_id']],
            [
                'equipo_local' => 'Equipo Local Genérico',
                'equipo_visita' => 'Equipo Visita Genérico',
                'estado' => 'JUGANDO'
            ]
        );

        // B. GUARDAR EL EVENTO EN HISTORIAL
        Evento::create([
            'partido_id' => $data['partido_id'],
            'tipo'       => $data['tipo'],
            'minuto'     => $data['minuto'],
            'descripcion'=> ($data['jugador'] ?? 'Juego') . ' (' . ($data['equipo'] ?? '-') . ')'
        ]);

        // C. ACTUALIZAR MARCADOR (Si es GOL)
        if ($data['tipo'] === 'GOL') {
            // Nota: Aquí simplificamos sumando al local.
            // En producción compararías: if ($data['equipo'] == $partido->equipo_local)...
            $partido->increment('goles_local');

            $this->info("    ⚽ ¡GOL! Marcador actualizado en MySQL para el partido " . $partido->id);
            $this->info("       Marcador actual: " . ($partido->goles_local + 1) . " - " . $partido->goles_visita);
        } else {
            $this->info("    ℹ️ Evento " . $data['tipo'] . " guardado.");
        }
    }
}
