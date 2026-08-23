<?php

namespace Tests\Feature;

use App\Models\Roles;
use App\Models\User;
use App\Models\WorkShift;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkTimeTrackingTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_employee_can_start_pause_resume_and_stop_own_shift(): void
    {
        $employee = $this->employee('master', 'own');

        Carbon::setTestNow('2026-08-24 09:00:00');
        $this->actingAs($employee)->postJson(route('work-time.start'))
            ->assertCreated()
            ->assertJsonPath('status', 'running');

        Carbon::setTestNow('2026-08-24 10:00:00');
        $this->actingAs($employee)->postJson(route('work-time.pause'))
            ->assertOk()
            ->assertJsonPath('status', 'paused')
            ->assertJsonPath('worked_seconds', 3600);

        Carbon::setTestNow('2026-08-24 10:30:00');
        $this->actingAs($employee)->postJson(route('work-time.resume'))
            ->assertOk()
            ->assertJsonPath('status', 'running');

        Carbon::setTestNow('2026-08-24 12:00:00');
        $this->actingAs($employee)->postJson(route('work-time.stop'))
            ->assertOk()
            ->assertJsonPath('status', 'inactive');

        $shift = WorkShift::with('breaks')->firstOrFail();
        $this->assertSame('completed', $shift->status);
        $this->assertSame(9000, $shift->workedSeconds());
        $this->assertCount(1, $shift->breaks);
        $this->assertNull($shift->active_user_id);
    }

    public function test_database_and_service_prevent_two_open_shifts_for_one_employee(): void
    {
        $employee = $this->employee('master', 'own');

        $this->actingAs($employee)->postJson(route('work-time.start'))->assertCreated();
        $this->actingAs($employee)->postJson(route('work-time.start'))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('shift');

        $this->assertSame(1, WorkShift::whereNotNull('active_user_id')->count());
    }

    public function test_only_authorized_all_scope_employee_can_open_work_time_reports(): void
    {
        $admin = $this->employee('super-admin', 'all');
        $master = $this->employee('master', 'own');

        $this->actingAs($master)->get(route('admin.work-time.index'))->assertForbidden();

        Carbon::setTestNow('2026-08-24 09:00:00');
        $this->actingAs($master)->postJson(route('work-time.start'))->assertCreated();
        Carbon::setTestNow('2026-08-24 10:00:00');
        $this->actingAs($master)->postJson(route('work-time.stop'))->assertOk();

        $this->actingAs($admin)->get(route('admin.work-time.index', [
            'date_from' => '2026-08-24',
            'date_to' => '2026-08-24',
        ]))->assertOk()->assertSee($master->name)->assertSee('1:00');

        $this->actingAs($admin)->get(route('admin.work-time.employee', [
            'member' => $master,
            'date_from' => '2026-08-24',
            'date_to' => '2026-08-24',
        ]))->assertOk()->assertSee(__('admin_work_time.daily_graph'));
    }

    private function employee(string $slug, string $scope): User
    {
        $role = Roles::firstOrCreate(
            ['slug' => $slug],
            [
                'name' => ucfirst(str_replace('-', ' ', $slug)),
                'appointment_scope' => $scope,
                'is_service_provider' => $slug === 'master',
                'is_system' => true,
            ],
        );

        return User::factory()->create([
            'role_id' => $role->id,
            'email_verified_at' => now(),
        ]);
    }
}
