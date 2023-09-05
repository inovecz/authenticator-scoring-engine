<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public static function up(): void
    {
        Schema::create('ml_mouse_successes', static function (Blueprint $table) {
            $table->unsignedSmallInteger('from');
            $table->unsignedSmallInteger('to');
            $table->unsignedDouble('ml_success_rate')->default(0.0);
            $table->primary(['from', 'to']);
        });
    }

    public static function down(): void
    {
        Schema::dropIfExists('ml_mouse_successes');
    }
};
