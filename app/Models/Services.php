<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Services extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 
        'price', 'price_can_change', 
        'duration_minutes_min', 'duration_minutes', 
        'status', 
        'short_description', 'full_description', 
        'eventColor', 
        'time_from', 'time_to', 'has_fixed_time',
    ];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_services', 'service_id', 'user_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointments::class, 'service_id');
    }

    public function translations()
    {
        return $this->hasMany(ServiceTranslation::class, 'service_id');
    }

    public function getTranslation($locale, $field)
    {
        return $this->translations()->where('locale', $locale)->value($field) ?? $this->translations()->where('locale', 'en')->value($field);
    }

    public function rules(): HasMany
    {
        return $this->hasMany(ServiceRule::class, 'service_id');
    }

    public function futureRules(): HasMany
    {
        return $this->hasMany(ServiceRule::class, 'service_id')
            ->whereDate('valid_from', '>', now()->toDateString())
            ->orderBy('valid_from');
    }

    public function ruleForDate(\Carbon\Carbon|string $date): ?\App\Models\ServiceRule
    {
        $date = $date instanceof \Carbon\Carbon ? $date->toDateString() : $date;

        return $this->rules()
            ->where('valid_from', '<=', $date)
            ->where(function ($q) use ($date) {
                $q->whereNull('valid_to')
                ->orWhere('valid_to', '>=', $date);
            })
            ->orderByDesc('valid_from')
            ->first();
    }
    public function effectivePriceForDate($date): float
    {
        $rule = $this->ruleForDate($date);

        if ($rule && $rule->price !== null) {
            return (float) $rule->price;
        }

        return (float) $this->price;
    }

}
