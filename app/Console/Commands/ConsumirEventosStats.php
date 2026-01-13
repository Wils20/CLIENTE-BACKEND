<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use App\Services\StatsService; // Importamos tu servicio

class ConsumirEventosStats extends Command
{
    // El nombre del comando para ejecutarlo en la terminal
    protected $signature = 'rabbitmq:consume-stats';

    // Descripción del comando
    protected $description = 'Escucha eventos de partidos y actualiza estadísticas';

    public function handle()
    {
        $this->info("Iniciando consumidor de Estadísticas...");

        // 1. Conexión a RabbitMQ (Asegúrate de que tus datos en .env sean correctos)
        $connection = new AMQPStreamConnection(
            env('RABBITMQ_HOST', '127.0.0.1'),
            env('RABBITMQ_PORT', 5672),
            env('RABBITMQ_USER', 'guest'),
            env('RABBITMQ_PASSWORD', 'guest')
        );

        $channel = $connection->channel();

        // 2. Declarar la cola (debe llamarse igual que donde envías los mensajes)
        // 'cola_eventos' es el nombre de la cola.
        $channel->queue_declare('cola_eventos', false, true, false, false);

        $this->info(" [*] Esperando eventos. Presiona CTRL+C para salir.");

        // 3. Definir qué hacer cuando llega un mensaje
        $callback = function ($msg) {
            $this->info(" [x] Recibido: " . $msg->body);

            try {
                // Decodificar el JSON recibido
                $data = json_decode($msg->body, true);

                // Validar que tengamos los datos necesarios
                if (isset($data['partido_id']) && isset($data['tipo_evento'])) {

                    // LLAMAR AL SERVICIO DE ESTADÍSTICAS
                    // Instanciamos el servicio aquí
                    $statsService = new StatsService();

                    // Ejecutamos la lógica de negocio
                    $statsService->registrarEvento(
                        $data['partido_id'],
                        $data['tipo_evento'],
                        $data['equipo'] ?? 'neutral'
                    );

                    $this->info(" [✓] Estadísticas actualizadas para partido ID: " . $data['partido_id']);
                }

                // Confirmar a RabbitMQ que procesamos el mensaje (ACK)
                $msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);

            } catch (\Exception $e) {
                $this->error(" [!] Error procesando mensaje: " . $e->getMessage());
                // Opcional: No enviar ACK si falló, para que RabbitMQ lo reintente
            }
        };

        // 4. Configurar el consumo
        $channel->basic_qos(null, 1, null); // Procesar 1 mensaje a la vez
        $channel->basic_consume('cola_eventos', '', false, false, false, false, $callback);

        // 5. Bucle infinito para escuchar
        while ($channel->is_consuming()) {
            $channel->wait();
        }

        $channel->close();
        $connection->close();
    }
}
