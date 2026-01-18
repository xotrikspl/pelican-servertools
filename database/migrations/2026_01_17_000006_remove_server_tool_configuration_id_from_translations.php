<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('server_tool_profile_translations', function (Blueprint $table) {
            if (Schema::hasColumn('server_tool_profile_translations', 'server_tool_configuration_id')) {
                $table->dropIndex('stpt_profile_locale_idx');
                $table->dropUnique('stpt_profile_locale_key_uidx');
                $table->dropColumn('server_tool_configuration_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('server_tool_profile_translations', function (Blueprint $table) {
            if (!Schema::hasColumn('server_tool_profile_translations', 'server_tool_configuration_id')) {
                $table->unsignedBigInteger('server_tool_configuration_id')->nullable()->after('translation_category_id');
                $table->index(['server_tool_configuration_id', 'locale'], 'stpt_profile_locale_idx');
                $table->unique(['server_tool_configuration_id', 'locale', 'key'], 'stpt_profile_locale_key_uidx');
            }
        });
    }
};
