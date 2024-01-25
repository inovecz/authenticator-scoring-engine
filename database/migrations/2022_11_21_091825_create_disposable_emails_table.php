<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public static function up(): void
    {
        Schema::create('disposable_emails', static function (Blueprint $table) {
            $table->string('domain')->index()->unique();
        });
    }

    public static function down(): void
    {
        Schema::dropIfExists('disposable_emails');
    }
};
