<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Customer extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'first_name', 'last_name', 'email', 'phone', 'password', 'account_registered_at', 'locale',
        'email_verified_at', 'phone_verified_at', 'marketing_consent', 'messaging_contacts',
    ];

    protected $hidden = ['password', 'remember_token'];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
            'account_registered_at' => 'datetime',
            'email_verified_at' => 'datetime',
            'phone_verified_at' => 'datetime',
            'marketing_consent' => 'boolean',
            'messaging_contacts' => 'array',
        ];
    }

    public function appointments()
    {
        return $this->hasMany(Appointments::class);
    }

    public function crmProfile() { return $this->hasOne(CustomerCrmProfile::class); }
    public function crmTags() { return $this->belongsToMany(CrmTag::class, 'customer_crm_tag'); }
    public function crmNotes() { return $this->hasMany(CustomerCrmNote::class); }
    public function consents() { return $this->hasMany(CustomerConsent::class); }
    public function documents() { return $this->hasMany(CustomerDocument::class); }
    public function preferredServices() { return $this->belongsToMany(Services::class, 'customer_preferred_services', 'customer_id', 'service_id'); }
    public function conversations() { return $this->hasMany(CrmConversation::class); }

    public function getFullNameAttribute(): string
    {
        return trim($this->first_name.' '.$this->last_name);
    }
}
