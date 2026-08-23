<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use PhpOffice\PhpSpreadsheet\Calculation\Web\Service;

class ServiceTranslation extends Model
{
    use HasFactory;

    protected $fillable = ['service_id', 'locale', 'is_inherited', 'name', 'short_description', 'full_description'];

    protected function casts(): array
    {
        return ['is_inherited' => 'boolean'];
    }

    public $timestamps = false;

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
