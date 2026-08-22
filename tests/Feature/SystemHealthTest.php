<?php

namespace Tests\Feature;

use App\Models\Roles;
use App\Models\SystemHealthRun;
use App\Models\User;
use App\Services\SystemHealth\Contracts\SystemHealthCheck;
use App\Services\SystemHealth\HealthCheckResult;
use App\Services\SystemHealth\SystemHealthMonitor;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class SystemHealthTest extends TestCase
{
    use RefreshDatabase;

    public function test_only_super_admin_role_can_view_or_run_internal_monitoring(): void
    {
        $admin = $this->staff('super-admin', 'all');
        $receptionist = $this->staff('receptionist', 'all');
        $master = $this->staff('master', 'own');

        $this->actingAs($admin)
            ->get(route('admin.system-health.index'))
            ->assertOk()
            ->assertSee(__('admin_system_health.title'));

        $this->actingAs($receptionist)
            ->get(route('admin.system-health.index'))
            ->assertForbidden();

        $this->actingAs($master)
            ->post(route('admin.system-health.run'))
            ->assertForbidden();
    }

    public function test_manual_monitoring_checks_critical_components_and_persists_results(): void
    {
        Storage::fake('local');
        $admin = $this->staff('super-admin', 'all');

        $this->actingAs($admin)
            ->post(route('admin.system-health.run'))
            ->assertRedirect(route('admin.system-health.index'))
            ->assertSessionHas('success');

        $run = SystemHealthRun::with('checks')->sole();

        $this->assertSame('manual', $run->source);
        $this->assertContains($run->status, ['operational', 'degraded']);
        $this->assertEqualsCanonicalizing(
            ['database', 'cache', 'storage', 'queue', 'booking_integrity'],
            $run->checks->pluck('key')->all(),
        );
        $this->assertTrue($run->checks->every(fn ($check) => $check->duration_ms >= 0));
    }

    public function test_one_failed_component_does_not_prevent_the_remaining_checks(): void
    {
        config()->set('system_health.checks', [FailingHealthCheck::class, HealthyHealthCheck::class]);

        $run = app(SystemHealthMonitor::class)->run('cli');

        $this->assertSame('outage', $run->status);
        $this->assertCount(2, $run->checks);
        $this->assertSame('failing', $run->checks->firstWhere('key', 'test_failure')->status);
        $this->assertSame('ok', $run->checks->firstWhere('key', 'test_healthy')->status);
    }

    public function test_dashboard_shows_daily_failure_history_and_stale_monitoring(): void
    {
        $admin = $this->staff('super-admin', 'all');
        $yesterday = now()->subDay()->setTime(10, 0);
        $today = now()->setTime(10, 0);

        $failedRun = SystemHealthRun::create([
            'status' => 'outage', 'source' => 'scheduled', 'duration_ms' => 900,
            'started_at' => $yesterday, 'finished_at' => $yesterday->copy()->addSecond(),
        ]);
        $failedRun->checks()->create([
            'key' => 'database', 'status' => 'failing', 'duration_ms' => 850,
            'message_key' => 'admin_system_health.messages.check_failed',
            'context' => ['exception' => 'RuntimeException'],
        ]);

        $healthyRun = SystemHealthRun::create([
            'status' => 'operational', 'source' => 'scheduled', 'duration_ms' => 25,
            'started_at' => $today, 'finished_at' => $today->copy()->addSecond(),
        ]);
        $healthyRun->checks()->create([
            'key' => 'database', 'status' => 'ok', 'duration_ms' => 10,
            'message_key' => 'admin_system_health.messages.database_ok',
        ]);

        $this->actingAs($admin)
            ->get(route('admin.system-health.index', ['date' => $yesterday->toDateString()]))
            ->assertOk()
            ->assertSee(__('admin_system_health.status.operational'))
            ->assertSee(__('admin_system_health.status.outage'))
            ->assertSee(__('admin_system_health.day_details', ['date' => $yesterday->toDateString()]));

        $healthyRun->update(['started_at' => now()->subMinutes(20)]);

        $this->actingAs($admin)
            ->get(route('admin.system-health.index'))
            ->assertOk()
            ->assertSee(__('admin_system_health.status.stale'));
    }

    private function staff(string $slug, string $scope): User
    {
        $role = Roles::create([
            'name' => ucfirst($slug).fake()->unique()->numerify('###'),
            'slug' => $slug,
            'appointment_scope' => $scope,
            'is_service_provider' => $slug === 'master',
            'is_system' => true,
        ]);

        return User::factory()->create([
            'username' => '@health_'.fake()->unique()->userName(),
            'role_id' => $role->id,
            'email_verified_at' => now(),
            'locale' => 'ru',
        ]);
    }
}

class FailingHealthCheck implements SystemHealthCheck
{
    public function key(): string
    {
        return 'test_failure';
    }

    public function run(): HealthCheckResult
    {
        return new HealthCheckResult($this->key(), 'failing', 1, 'admin_system_health.messages.check_failed', ['exception'=>'ExpectedFailure']);
    }
}

class HealthyHealthCheck implements SystemHealthCheck
{
    public function key(): string
    {
        return 'test_healthy';
    }

    public function run(): HealthCheckResult
    {
        return new HealthCheckResult($this->key(), 'ok', 1, 'admin_system_health.messages.ok');
    }
}
