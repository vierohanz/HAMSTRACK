<?php

namespace Database\Seeders;

use App\Models\User;
use Carbon\Carbon;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $data = [];

        for ($i = 0; $i < 20; $i++) {
            $data[] = [
                'temperature' => mt_rand(2500, 3500) / 100,
                'humidity' => mt_rand(6000, 9000) / 100,
                'wind_speed' => mt_rand(100, 500) / 100,
                'wind_direction' => mt_rand(0, 36000) / 100,
                'rainfall' => mt_rand(0, 1000) / 100,
                'irradiance' => mt_rand(100, 1000) / 1,
                'atmospheric_pressure' => mt_rand(98000, 105000) / 100,
                'created_at' => Carbon::create(2025, 6, 7, 0, 0, 0),
                'updated_at' => Carbon::create(2025, 6, 7, 0, 0, 0),
            ];
        }

        DB::table('collect')->insert($data);
    }
}
