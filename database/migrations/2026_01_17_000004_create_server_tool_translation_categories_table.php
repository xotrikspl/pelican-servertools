<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('server_tool_translation_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique('sttc_slug_uidx');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('server_tool_translation_categories');
    }
};
