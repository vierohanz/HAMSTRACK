<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('collect', function (Blueprint $table) {
            $table->id();
            $table->float('temperature', 5, 2)->nullable(true);
            $table->float('humidity', 5, 2)->nullable(true);
            $table->float('wind_speed', 5, 2)->nullable(true);
            $table->float('wind_direction', 5, 2)->nullable(true);
            $table->float('rainfall', 5, 2)->nullable(true);
            $table->float('irradiance', 5, 2)->nullable(true);
            $table->float('atmospheric_pressure', 5, 2)->nullable(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('collect');
    }
};
