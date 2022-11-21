<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('login_attemps', static function (Blueprint $table) {
            $table->id();
            $table->string('entity', 32)->index();
            $table->string('country_code', 4)->nullable();
            $table->string('country', 128)->nullable();
            $table->string('region', 128)->nullable();
            $table->string('city', 128)->nullable();
            $table->float('longitude')->nullable();
            $table->float('latitude')->nullable();
            $table->string('ip', 15)->nullable();
            $table->string('device')->nullable();
            $table->string('os')->nullable();
            $table->string('browser')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_attemps');
    }
};
