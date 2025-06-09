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
        Schema::create('artificial_intellegence', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date');
            $table->float('irradiance', 5, 2)->nullable(true);
            $table->float('temperature_c', 5, 2)->nullable(true);
            $table->float('precipitation_mm_per_hr', 5, 2)->nullable(true);
            $table->float('humidity_percent', 5, 2)->nullable(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('artificial_intellegence');
    }
};
