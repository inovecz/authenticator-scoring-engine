<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public static function up(): void
    {
        Schema::create('ip_addresses', function (Blueprint $table) {
            $table->string('ip', 15)->primary()->index();
            $table->unsignedBigInteger('attempts')->default(0);
            $table->unsignedBigInteger('successful_attempts')->default(0);
            $table->double('success_rate')->default(0.0);
            $table->unsignedDouble('ml_success_rate')->default(0.0);
            $table->timestamps();
        });
    }

    public static function down(): void
    {
        Schema::dropIfExists('ip_addresses');
    }
};
