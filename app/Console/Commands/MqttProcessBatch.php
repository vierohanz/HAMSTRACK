<?php

namespace App\Console\Commands;

use App\Models\Collect;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Redis;
use Illuminate\Support\Facades\Log;

class MqttProcessBatch extends Command
{
    protected $signature = 'mqtt:process-batch';
    protected $description = 'Saves whatever data is in the MQTT buffer every 300 seconds.';

    const BATCH_INTERVAL = 300;

    public function handle()
    {
        $this->info("🚀 Starting MQTT Batch Processor. Will save data every " . self::BATCH_INTERVAL . " seconds.");

        while (true) {
            $this->comment("Sleeping for " . self::BATCH_INTERVAL . " seconds... (" . now()->format('H:i:s') . ")");
            sleep(self::BATCH_INTERVAL);

            $this->info("Waking up! Checking buffer...");
            $dataToSave = Redis::hGetAll('mqtt_data_buffer');

            if (empty($dataToSave)) {
                $this->line("Buffer is empty. Nothing to save.");
                continue;
            }
            $this->warn("Found data in buffer. Processing and saving...");
            try {
                Redis::del('mqtt_data_buffer');
                $dataToSave['created_at'] = now();
                $dataToSave['updated_at'] = now();
                foreach ($dataToSave as $key => $value) {
                    if (!in_array($key, ['created_at', 'updated_at'])) {
                        $dataToSave[$key] = (float)$value;
                    }
                }

                Collect::create($dataToSave);
                $this->info("✅ Batch data saved successfully: " . json_encode($dataToSave));
            } catch (\Exception $e) {
                $this->error("❌ Failed to save batch data: " . $e->getMessage());
                Log::error("MQTT Batch Save Error: " . $e->getMessage(), $dataToSave);
            }
        }
    }
}
