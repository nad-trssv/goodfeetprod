<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use PhpOffice\PhpSpreadsheet\Calculation\Web\Service;

class ServiceTranslation extends Model
{
    use HasFactory;

    protected $fillable = ['service_id', 'locale', 'name', 'short_description', 'full_description'];

    public $timestamps = false;

    public function service()
    {
        return $this->belongsTo(Service::class);
    }
}
