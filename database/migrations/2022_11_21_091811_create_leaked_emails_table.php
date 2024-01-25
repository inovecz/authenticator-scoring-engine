<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public static function up(): void
    {
        Schema::create('leaked_emails', static function (Blueprint $table) {
            $table->string('email', 256)->index()->unique();
            $table->unsignedInteger('leaks')->default(1);
        });
    }

    public static function down(): void
    {
        Schema::dropIfExists('leaked_emails');
    }
};
