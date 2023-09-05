<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public static function up(): void
    {
        Schema::table('login_attempts', function (Blueprint $table) {
            $table->after('action_data', function (Blueprint $table) {
                $table->unsignedDouble('timer')->nullable();
                $table->unsignedDouble('mouse_max_speed')->nullable();
                $table->unsignedDouble('mouse_avg_speed')->nullable();
                $table->unsignedDouble('mouse_max_accel')->nullable();
                $table->unsignedDouble('mouse_avg_accel')->nullable();
                $table->unsignedBigInteger('mouse_movement')->nullable();
                $table->unsignedBigInteger('mouse_clicks')->nullable();
                $table->unsignedBigInteger('mouse_selections')->nullable();
                $table->unsignedBigInteger('mouse_scrolls')->nullable();
            });
        });
    }

    public static function down(): void
    {
        Schema::table('login_attempts', function (Blueprint $table) {
            $table->dropColumn([
                'timer',
                'mouse_max_speed',
                'mouse_avg_speed',
                'mouse_max_accel',
                'mouse_avg_accel',
                'mouse_movement',
                'mouse_clicks',
                'mouse_selections',
                'mouse_scrolls',
            ]);
        });
    }
};
