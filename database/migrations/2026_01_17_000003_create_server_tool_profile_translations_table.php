<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('server_tool_profile_translations', function (Blueprint $table) {
            $table->id();
            $table->string('locale', 10);
            $table->string('key');
            $table->text('value');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('server_tool_profile_translations');
    }
};