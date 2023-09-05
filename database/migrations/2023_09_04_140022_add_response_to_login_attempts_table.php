<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public static function up(): void
    {
        Schema::table('login_attempts', function (Blueprint $table) {
            $table->json('response')->nullable()->after('mouse_scrolls');
        });
    }

    public static function down(): void
    {
        Schema::table('login_attempts', function (Blueprint $table) {
            $table->dropColumn('response');
        });
    }
};
