<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration {
    public function up()
    {
        Schema::create('blacklists', function (Blueprint $table) {
            $table->id();
            $table->enum('type', \App\Enums\BlacklistTypeEnum::values());
            $table->json('value');
            $table->text('reason')->nullable();
            $table->boolean('active')->default(true);
            $table->timestamps();

            $table->unique(['type', 'value']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('blacklists');
    }
};
