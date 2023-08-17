<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public static function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->string('ip', 15)->primary();
            $table->unsignedBigInteger('attempts')->default(0);
            $table->unsignedBigInteger('successful_attempts')->default(0);
            $table->double('success_rate')->default(0.0);
            $table->string('city', 128)->nullable();
            $table->string('region', 128)->nullable();
            $table->string('country', 128)->nullable();
            $table->string('country_code', 4)->nullable();
            $table->double('longitude')->nullable();
            $table->double('latitude')->nullable();
            $table->timestamps();
        });
    }

    public static function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
