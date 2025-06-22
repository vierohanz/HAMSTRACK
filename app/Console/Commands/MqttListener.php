<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use PhpMqtt\Client\MqttClient;

class MqttListener extends Command
{
    protected $signature = 'mqtt:listen';
    protected $description = 'Listen to MQTT messages and push them to a Redis buffer.';

    public function handle()
    {
        $host = env('MQTT_BROKER_HOST', 'localhost');
        $port = env('MQTT_BROKER_PORT', 1883);
        $clientId = env('MQTT_CLIENT_ID', 'hamstrack-listener-' . uniqid());

        try {
            $mqtt = new MqttClient($host, $port, $clientId);
            $mqtt->connect();
            $this->info("✅ Connected to MQTT Broker. Listening for messages...");

            $mqtt->subscribe('#', function (string $topic, string $message) {
                $this->line("Received topic: <fg=cyan>$topic</> | Message: <fg=yellow>$message</>");

                $decodedMessage = json_decode($message);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return;
                }

                $metricKey = null;
                switch (true) {
                    case str_starts_with($topic, 'PLN_NP_Testing_Iradian_14'):
                        $metricKey = 'irradiance';
                        break;
                    case str_starts_with($topic, 'PLN_NP_Testing_Temperature_14'):
                        $metricKey = 'temperature';
                        break;
                    case str_starts_with($topic, 'PLN_NP_Testing_Humidity_14'):
                        $metricKey = 'humidity';
                        break;
                    case str_starts_with($topic, 'PLN_NP_Testing_AtmosphericPressure_14'):
                        $metricKey = 'atmospheric_pressure';
                        break;
                    case str_starts_with($topic, 'PLN_NP_Testing_WindSpeed_14'):
                        $metricKey = 'wind_speed';
                        break;
                    case str_starts_with($topic, 'PLN_NP_Testing_WindDirection_14'):
                        $metricKey = 'wind_direction';
                        break;
                    case str_starts_with($topic, 'PLN_NP_Testing_RainFall_'):
                        $metricKey = 'rainfall';
                        break;
                }

                if ($metricKey && isset($decodedMessage->$metricKey)) {
                    $value = (float) $decodedMessage->$metricKey;
                    Redis::hSet('mqtt_data_buffer', $metricKey, $value);
                    $this->comment("   -> '{$metricKey}' ({$value}) cached.");
                }
            }, 0);

            // Loop ini akan berjalan selamanya, dan hanya fokus mendengarkan.
            $mqtt->loop(true);
        } catch (\Exception $e) {
            $this->error("❌ MQTT Listener Error: " . $e->getMessage());
        }
    }
}
