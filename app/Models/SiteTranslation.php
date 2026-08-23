<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SiteTranslation extends Model
{
    protected $fillable = ['locale', 'translation_key', 'value', 'is_inherited'];

    protected function casts(): array
    {
        return ['is_inherited' => 'boolean'];
    }
}
