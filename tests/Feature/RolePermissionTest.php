<?php

namespace Tests\Feature;

use App\Models\Permission;
use App\Models\Roles;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RolePermissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_manage_roles_and_permissions(): void
    {
        $adminRole = Roles::create([
            'name' => 'Super Admin', 'slug' => 'super-admin', 'appointment_scope' => 'all',
            'is_service_provider' => true, 'is_system' => true,
        ]);
        $admin = User::factory()->create(['username' => '@rbac_admin', 'role_id' => $adminRole->id, 'email_verified_at' => now()]);

        $this->actingAs($admin)->get(route('admin.roles.index'))
            ->assertOk()
            ->assertSee(__('admin_roles.title'));

        $this->actingAs($admin)->post(route('admin.roles.store'), [
            'name' => 'Branch manager',
            'appointment_scope' => 'all',
            'is_service_provider' => '0',
        ])->assertRedirect();

        $role = Roles::where('name', 'Branch manager')->firstOrFail();
        $this->actingAs($admin)->put(route('admin.roles.update', $role), [
            'name' => $role->name,
            'appointment_scope' => 'all',
            'permissions' => ['appointments.view', 'customers.view'],
        ])->assertRedirect();

        $this->assertEqualsCanonicalizing(
            ['appointments.view', 'customers.view'],
            $role->fresh()->permissions()->pluck('key')->all(),
        );
    }

    public function test_role_without_permission_gets_forbidden_response(): void
    {
        $role = Roles::create(['name' => 'Limited', 'slug' => 'limited', 'appointment_scope' => 'own']);
        $role->permissions()->attach(Permission::where('key', 'profile.view')->value('id'));
        $user = User::factory()->create(['username' => '@rbac_limited', 'role_id' => $role->id, 'email_verified_at' => now()]);

        $this->actingAs($user)->get(route('admin.roles.index'))
            ->assertForbidden()
            ->assertSee(__('admin_roles.access_denied'));

        $this->actingAs($user)->getJson(route('calendar.customers.search', ['query' => 'Anna']))
            ->assertForbidden()
            ->assertJsonPath('message', __('admin_roles.access_denied'));
    }

    public function test_receptionist_can_request_any_master_but_master_cannot(): void
    {
        $receptionRole = Roles::create([
            'name' => 'Receptionist', 'slug' => 'receptionist', 'appointment_scope' => 'all',
        ]);
        $receptionRole->permissions()->sync(Permission::whereIn('key', ['appointments.create', 'customers.view'])->pluck('id'));
        $receptionist = User::factory()->create(['username' => '@rbac_reception', 'role_id' => $receptionRole->id, 'email_verified_at' => now()]);

        $masterRole = Roles::create([
            'name' => 'Master', 'slug' => 'master', 'appointment_scope' => 'own', 'is_service_provider' => true,
        ]);
        $master = User::factory()->create(['username' => '@rbac_master', 'role_id' => $masterRole->id, 'email_verified_at' => now()]);

        $payload = ['service_id' => 999999, 'user_id' => 'all', 'date' => now()->addDay()->toDateString()];

        $this->actingAs($receptionist)->postJson(route('calendar.availability'), $payload)
            ->assertUnprocessable();

        $this->actingAs($master)->postJson(route('calendar.availability'), $payload)
            ->assertForbidden();

        $this->actingAs($master)->postJson(route('calendar.store'), ['user_id' => $receptionist->id])
            ->assertForbidden();
    }

    public function test_saved_role_settings_override_the_initial_defaults(): void
    {
        $role = Roles::create([
            'name' => 'Master',
            'slug' => 'master',
            'appointment_scope' => 'own',
            'is_service_provider' => true,
        ]);
        $user = User::factory()->create([
            'username' => '@rbac_configurable_master',
            'role_id' => $role->id,
            'email_verified_at' => now(),
        ]);

        $this->assertTrue($user->hasPermission('appointments.create'));
        $this->assertFalse($user->hasAllAppointmentsScope());
        $this->assertTrue($user->isServiceProvider());

        $role->permissions()->sync([]);
        $role->update(['appointment_scope' => 'all', 'is_service_provider' => false]);
        $user->unsetRelation('role');

        $this->assertFalse($user->hasPermission('appointments.create'));
        $this->assertTrue($user->hasAllAppointmentsScope());
        $this->assertFalse($user->isServiceProvider());
    }
}
