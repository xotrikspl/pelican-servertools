<?php

namespace Xotriks\Servertools\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Xotriks\Servertools\Models\ServerToolTranslationCategory;

class ServerToolProfileTranslation extends Model
{
    protected $table = 'server_tool_profile_translations';

    protected $fillable = [
        'translation_category_id',
        'locale',
        'key',
        'value',
    ];

    public function translationCategory(): BelongsTo
    {
        return $this->belongsTo(ServerToolTranslationCategory::class, 'translation_category_id');
    }
}