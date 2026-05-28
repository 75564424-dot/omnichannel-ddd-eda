<?php

declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('middleware_registered_modules', function (Blueprint $table) {
            $table->id();
            $table->string('logical_id');
            $table->string('type', 16);
            $table->string('name');
            $table->json('event_types');
            $table->timestamps();

            $table->unique(['logical_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('middleware_registered_modules');
    }
};
