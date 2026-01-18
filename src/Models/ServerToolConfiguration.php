<?php

namespace Xotriks\Servertools\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Egg;
use Xotriks\Servertools\Models\ServerToolTranslationCategory;

class ServerToolConfiguration extends Model
{
    protected $table = 'server_tool_configurations';

    protected $fillable = [
        'egg_id',
        'name',
        'profile_name',
        'description',
        'config',
        'server_tools_enabled',
        'translation_category_id',
    ];

    protected $casts = [
        'config' => 'array',
        'server_tools_enabled' => 'boolean',
    ];

    public function egg(): BelongsTo
    {
        return $this->belongsTo(Egg::class);
    }

    public function translationCategory(): BelongsTo
    {
        return $this->belongsTo(ServerToolTranslationCategory::class, 'translation_category_id');
    }

}
