<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Model;

class SiteSettings extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'group', 
        'key', 
        'payload',
    ];

    public function scopeGroup($query, $groupName)
    {
        return $query->where('group', $groupName);
    }

    /**
     * Метод для удобного создания записей с группой.
     */
    public static function group(string $groupName)
    {
        return new class($groupName) {
            protected $groupName;

            public function __construct($groupName)
            {
                $this->groupName = $groupName;
            }

            public function create(array $data)
            {
                foreach ($data as $key => $value) {
                    SiteSettings::create([
                        'key' => $key,
                        'payload' => $value,
                        'group' => $this->groupName,
                    ]);
                }
            }
        };
    }
}
