<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('server_tool_configurations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('egg_id')->nullable();
            $table->string('name');
            $table->string('profile_name')->nullable();
            $table->text('description')->nullable();
            $table->json('config')->nullable();
            $table->timestamps();

            // Indeksy
            $table->index('egg_id');
            // Note: FK constraint to eggs table skipped due to type incompatibility
            // egg_id is just a reference to Pterodactyl egg, not enforced as FK
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('server_tool_configurations');
    }
};
