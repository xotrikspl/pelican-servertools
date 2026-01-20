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
            $table->boolean('server_tools_enabled')->default(false);
            $table->unsignedBigInteger('translation_category_id')->nullable();
            $table->timestamps();

            $table->index('egg_id');
            $table->index('translation_category_id', 'stc_translation_category_idx');
        });

        Schema::create('server_tool_translation_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique('sttc_slug_uidx');
            $table->timestamps();
        });

        Schema::create('server_tool_profile_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('translation_category_id')->nullable();
            $table->string('locale', 10);
            $table->string('key');
            $table->text('value');
            $table->timestamps();

            $table->index('translation_category_id', 'stpt_translation_category_idx');
            $table->unique(['translation_category_id', 'locale', 'key'], 'stpt_cat_locale_key_uidx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('server_tool_profile_translations');
        Schema::dropIfExists('server_tool_translation_categories');
        Schema::dropIfExists('server_tool_configurations');
    }
};
