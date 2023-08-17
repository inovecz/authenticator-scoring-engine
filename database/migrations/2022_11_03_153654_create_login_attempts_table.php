<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('login_attempts', static function (Blueprint $table) {
            $table->id();
            $table->string('entity', 32)->nullable()->index();
            $table->boolean('js_run')->default(false);
            $table->boolean('is_google')->default(false);
            $table->unsignedSmallInteger('status_code')->default(0);
            $table->boolean('new_visit')->default(false);
            $table->text('url')->nullable();
            $table->text('referer')->nullable();
            $table->string('ip', 15)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('device')->nullable();
            $table->string('os')->nullable();
            $table->string('browser')->nullable();
            $table->boolean('successful')->default(false);
            $table->string('action')->nullable();
            $table->text('action_description')->nullable();
            $table->text('action_data')->nullable();
            $table->timestamps();

            $table->foreign('ip')->references('ip')->on('locations');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('login_attempts');
    }
};
