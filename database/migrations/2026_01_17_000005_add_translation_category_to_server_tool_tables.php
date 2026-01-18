<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('server_tool_configurations', function (Blueprint $table) {
            if (!Schema::hasColumn('server_tool_configurations', 'translation_category_id')) {
                $table->unsignedBigInteger('translation_category_id')->nullable()->after('server_tools_enabled');
                $table->index('translation_category_id', 'stc_translation_category_idx');
            }
        });

        Schema::table('server_tool_profile_translations', function (Blueprint $table) {
            if (!Schema::hasColumn('server_tool_profile_translations', 'translation_category_id')) {
                $table->unsignedBigInteger('translation_category_id')->nullable()->after('id');
                $table->index('translation_category_id', 'stpt_translation_category_idx');
            }
        });

        Schema::table('server_tool_profile_translations', function (Blueprint $table) {
            $table->unique(['translation_category_id', 'locale', 'key'], 'stpt_cat_locale_key_uidx');
        });
    }

    public function down(): void
    {
        Schema::table('server_tool_profile_translations', function (Blueprint $table) {
            $table->dropUnique('stpt_cat_locale_key_uidx');
            $table->dropIndex('stpt_translation_category_idx');
            $table->dropColumn('translation_category_id');
        });

        Schema::table('server_tool_configurations', function (Blueprint $table) {
            $table->dropIndex('stc_translation_category_idx');
            $table->dropColumn('translation_category_id');
        });
    }
};
