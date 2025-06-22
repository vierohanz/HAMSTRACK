<?php

namespace App\Console\Commands;

use App\Models\Collect;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class MqttProcessBatch extends Command
{
    protected $signature = 'mqtt:process-batch';
    protected $description = 'Saves data from the MQTT buffer at every 5th minute (e.g., 00, 05, 10, ...).';

    public function handle()
    {
        $this->info("🚀 Starting MQTT Batch Processor. Will save data at every minute divisible by 5 (e.g., 00, 05, 10...).");

        $lastSavedMinute = null;

        while (true) {
            $now = now();
            $minute = (int) $now->format('i');
            $second = (int) $now->format('s');

            if ($minute % 5 === 0 && $second === 0 && $minute !== $lastSavedMinute) {
                $this->info("⏰ Time matched at " . $now->format('H:i:s') . " — processing data...");

                $dataToSave = Redis::hGetAll('mqtt_data_buffer');

                if (empty($dataToSave)) {
                    $this->line("📭 Buffer is empty. Nothing to save.");
                } else {
                    $this->warn("📦 Found data in buffer. Saving...");

                    try {
                        Redis::del('mqtt_data_buffer');

                        $dataToSave['created_at'] = now();
                        $dataToSave['updated_at'] = now();

                        foreach ($dataToSave as $key => $value) {
                            if (!in_array($key, ['created_at', 'updated_at'])) {
                                $dataToSave[$key] = (float) $value;
                            }
                        }

                        Collect::create($dataToSave);
                        $this->info("✅ Data saved: " . json_encode($dataToSave));
                    } catch (\Exception $e) {
                        $this->error("❌ Failed to save batch: " . $e->getMessage());
                        Log::error("MQTT Batch Save Error: " . $e->getMessage(), $dataToSave);
                    }
                }

                $lastSavedMinute = $minute;
            }

            usleep(200_000); // 0.2 detik, untuk menjaga loop tetap ringan
        }
    }
}
