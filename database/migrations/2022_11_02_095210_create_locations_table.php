<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public static function up(): void
    {
        Schema::create('locations', function (Blueprint $table) {
            $table->id();
            $table->string('city', 128)->index()->nullable();
            $table->string('region', 128)->nullable();
            $table->string('country', 128)->nullable();
            $table->string('country_code', 4)->nullable();
            $table->double('longitude')->nullable();
            $table->double('latitude')->nullable();
            $table->unsignedBigInteger('attempts')->default(0);
            $table->unsignedBigInteger('successful_attempts')->default(0);
            $table->double('success_rate')->default(0.0);
            $table->unsignedDouble('ml_success_rate')->default(0.0);
            $table->timestamps();
        });
    }

    public static function down(): void
    {
        Schema::dropIfExists('locations');
    }
};
