<?php

namespace Xotriks\Servertools\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServerToolTranslationCategory extends Model
{
    protected $table = 'server_tool_translation_categories';

    protected $fillable = [
        'name',
        'slug',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(ServerToolProfileTranslation::class, 'translation_category_id');
    }
}
