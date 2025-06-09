<?php

namespace App\Console\Commands;

use App\Models\collect;
use Illuminate\Console\Command;
use PhpMqtt\Client\MqttClient;
use PhpMqtt\Client\ConnectionSettings;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class MqttListener extends Command
{
    protected $signature = 'mqtt:listen';
    protected $description = 'Listen to MQTT messages and store data to the database';

    public function handle()
    {
        $host = env('MQTT_BROKER_HOST', 'localhost');
        $port = env('MQTT_BROKER_PORT', 1883);
        $clientId = env('MQTT_CLIENT_ID', 'hamstrack');

        $connectionSettings = (new ConnectionSettings)
            ->setUsername(env('MQTT_BROKER_USERNAME'))
            ->setPassword(env('MQTT_BROKER_PASSWORD'))
            ->setKeepAliveInterval(60)
            ->setUseTls(false);

        try {
            $mqtt = new MqttClient($host, $port, $clientId);
            $mqtt->connect($connectionSettings);
            echo "Connected to MQTT Broker!" . PHP_EOL;

            $mqtt->subscribe('#', function (string $topic, string $message) {
                echo "Received topic: $topic | Message: $message\n";
                $now = now()->format('Y-m-d H:i');
                $cacheKey = "weather_data_$now";
                $data = Cache::get($cacheKey, []);

                // Deteksi topic dan simpan value sesuai jenis
                switch (true) {
                    case str_starts_with($topic, 'PLN_NP_Testing_Iradian_'):
                        $data['irradiance'] = (float) $message;
                        break;
                    case str_starts_with($topic, 'PLN_NP_Testing_WindSpeed_'):
                        $data['wind_speed'] = (float) $message;
                        break;
                    case str_starts_with($topic, 'PLN_NP_Testing_WindDirection_'):
                        $data['wind_direction'] = (float) $message;
                        break;
                    case str_starts_with($topic, 'PLN_NP_Testing_Temperature_'):
                        $data['temperature'] = (float) $message;
                        break;
                    case str_starts_with($topic, 'PLN_NP_Testing_Humidity_'):
                        $data['humidity'] = (float) $message;
                        break;
                    case str_starts_with($topic, 'PLN_NP_Testing_AtmosphericPressure_'):
                        $data['pressure'] = (float) $message;
                        break;
                    case str_starts_with($topic, 'PLN_NP_Testing_RainFall_'):
                        $data['rainfall'] = (float) $message;
                        break;
                }

                // Simpan kembali ke cache
                Cache::put($cacheKey, $data, now()->addMinutes(2));

                $requiredFields = ['irradiance', 'wind_speed', 'wind_direction', 'temperature', 'humidity', 'pressure', 'rainfall'];
                if (count(array_intersect_key(array_flip($requiredFields), $data)) === count($requiredFields)) {
                    if (!Cache::has("weather_saved_$now")) {
                        collect::create($data);
                        Cache::put("weather_saved_$now", true, now()->addMinutes(2));
                        echo "Data saved at $now: " . json_encode($data) . PHP_EOL;
                    }
                }
            }, 0);

            $mqtt->loop(true);
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage() . PHP_EOL;
            Log::error("Error connecting to MQTT broker: " . $e->getMessage());
        }
    }
}
