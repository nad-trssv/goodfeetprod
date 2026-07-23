<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Fortify\TwoFactorAuthenticatable;
use Laravel\Jetstream\HasProfilePhoto;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory;
    use HasProfilePhoto;
    use Notifiable;
    use TwoFactorAuthenticatable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'username',
        'name',
        'professional_titles',
        'phone',
        'email',
        'role_id',
        'last_active',
        'profile_photo_path',
        'date_birthday',
        'password'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_recovery_codes',
        'two_factor_secret',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array<int, string>
     */

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_active' => 'datetime',
            'password' => 'hashed',
            'professional_titles' => 'array',
        ];
    }

    public function professionalTitle(?string $locale = null): ?string
    {
        $titles = $this->professional_titles ?? [];
        $locale ??= app()->getLocale();

        return $titles[$locale]
            ?? $titles[config('app.fallback_locale')]
            ?? collect($titles)->first(fn ($title) => filled($title));
    }

    function SocialProviders() {
        return $this->hasMany(SocialProvider::class);
    }

    public function role()
    {
        return $this->belongsTo(Roles::class);
    }

    public function services()
    {
        return $this->belongsToMany(Services::class, 'user_services', 'user_id', 'service_id');
    }

    public function appointments()
    {
        return $this->hasMany(Appointments::class);
    }

    public function isOnline():bool
    {
        return $this->last_active && $this->last_active->diffInMinutes(now()) < 5;
    }

    public function lastSeen(): string
    {
        if (!$this->last_active) {
            return 'Never active';
        }

        if ($this->last_active->isFuture()) {
            return 'Invalid timestamp';
        }

        if ($this->last_active->diffInMinutes(now()) < 5) {
            return 'Online';
        }

        $diff = abs((int) now()->diffInMinutes($this->last_active));

        if ($diff < 60) {
            return $diff . ' мин. назад';
        }

        if ($diff < 1440) {
            $hours = abs((int) now()->diffInHours($this->last_active));
            return $hours . ' ч. назад';
        }

        $days = abs((int) now()->diffInDays($this->last_active));
        return $days . ' дн. назад';
    }

    public function schedule()
    {
        return $this->hasOne(UserSchedule::class);
    }
    
    public function rooms()
    {
        return $this->belongsToMany(Room::class, 'room_user');
    }

    public function notificationRecipients()
    {
        return $this->hasMany(UserNotificationRecipient::class, 'master_id');
    }

    public function notificationRecipientsUsers()
    {
        return $this->belongsToMany(User::class, 'user_notification_recipients', 'master_id', 'recipient_id');
    }
}
