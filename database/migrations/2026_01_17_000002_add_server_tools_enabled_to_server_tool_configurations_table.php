<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('server_tool_configurations', function (Blueprint $table) {
            $table->boolean('server_tools_enabled')->default(false)->after('config');
        });
    }

    public function down(): void
    {
        Schema::table('server_tool_configurations', function (Blueprint $table) {
            $table->dropColumn('server_tools_enabled');
        });
    }
};