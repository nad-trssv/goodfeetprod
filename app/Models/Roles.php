<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;

class Roles extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'appointment_scope', 'is_service_provider', 'is_system'];

    protected function casts(): array
    {
        return ['is_service_provider' => 'boolean', 'is_system' => 'boolean'];
    }

    protected static function booted(): void
    {
        static::creating(function (Roles $role): void {
            $defaults = config('role_defaults.'.$role->resolvedSlug());
            if (! is_array($defaults)) {
                return;
            }

            if (! $role->isDirty('appointment_scope')) {
                $role->appointment_scope = $defaults['appointment_scope'];
            }
            if (! $role->isDirty('is_service_provider')) {
                $role->is_service_provider = $defaults['is_service_provider'];
            }
        });

        static::created(function (Roles $role): void {
            if (! Schema::hasTable('permissions') || ! Schema::hasTable('permission_role')) {
                return;
            }

            $defaults = config('role_defaults.'.$role->resolvedSlug());
            if (! is_array($defaults)) {
                return;
            }

            $permissionIds = Permission::query()
                ->whereIn('key', $defaults['permissions'] ?? [])
                ->pluck('id');

            $role->permissions()->syncWithoutDetaching($permissionIds);
        });
    }

    public function users()
    {
        return $this->hasMany(User::class, 'role_id');
    }

    public function permissions()
    {
        return $this->belongsToMany(Permission::class, 'permission_role', 'role_id', 'permission_id');
    }

    public function resolvedSlug(): string
    {
        if (filled($this->slug)) {
            return $this->slug;
        }

        $name = mb_strtolower(trim($this->name));

        return match (true) {
            in_array($name, ['admin', 'super admin', 'super-admin'], true) => 'super-admin',
            in_array($name, ['master', 'мастер'], true) => 'master',
            in_array($name, ['klient', 'client', 'customer', 'клиент'], true) => 'customer',
            (int) $this->id === 1 => 'super-admin',
            (int) $this->id === 2 => 'master',
            default => $this->slug ?: 'custom',
        };
    }

    public function displayName(): string
    {
        $key = 'admin_roles.system_roles.'.$this->resolvedSlug();

        return trans()->has($key) ? __($key) : $this->name;
    }
}
