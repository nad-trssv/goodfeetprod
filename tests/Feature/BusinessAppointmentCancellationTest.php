<?php

namespace Tests\Feature;

use App\Models\Appointments;
use App\Models\Roles;
use App\Models\Services;
use App\Models\User;
use App\Services\AppointmentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BusinessAppointmentCancellationTest extends TestCase
{
    use RefreshDatabase;

    public function test_master_can_cancel_own_appointment_without_deleting_it(): void
    {
        [$admin, $master, , $appointment] = $this->records();

        $this->actingAs($master)->deleteJson(route('calendar.destroy', $appointment), [
            'reason' => 'Master became unavailable',
        ])->assertOk()->assertJsonPath('message', __('admin_appointments.cancelled'));

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'cancelled_by_business',
            'slot_lock_key' => null,
        ]);
        $this->assertDatabaseHas('appointment_status_histories', [
            'appointment_id' => $appointment->id,
            'from_status' => 'confirmed',
            'to_status' => 'cancelled_by_business',
            'changed_by_type' => $master->getMorphClass(),
            'changed_by_id' => $master->id,
            'reason' => 'Master became unavailable',
        ]);
        $this->assertNotNull($appointment->fresh());
        $this->assertCount(1, $admin->notifications);
    }

    public function test_cancellation_requires_a_meaningful_reason(): void
    {
        [, $master, , $appointment] = $this->records();

        $this->actingAs($master)->deleteJson(route('calendar.destroy', $appointment), ['reason' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('reason');

        $this->assertSame('confirmed', $appointment->fresh()->status);
    }

    public function test_master_cannot_cancel_another_masters_appointment(): void
    {
        [, , $otherMaster, $appointment] = $this->records();

        $this->actingAs($otherMaster)->deleteJson(route('calendar.destroy', $appointment), [
            'reason' => 'Not my appointment',
        ])->assertForbidden();

        $this->assertSame('confirmed', $appointment->fresh()->status);
    }

    public function test_super_admin_can_cancel_any_appointment_and_it_disappears_from_active_calendar(): void
    {
        [$admin, , , $appointment] = $this->records();

        $this->actingAs($admin)->deleteJson(route('calendar.destroy', $appointment), [
            'reason' => 'Business is closed',
        ])->assertOk();

        $this->actingAs($admin);
        $calendarAppointmentIds = collect(app(AppointmentService::class)->list('superAdmin')['appointments'])
            ->pluck('id');

        $this->assertNotContains($appointment->id, $calendarAppointmentIds);
    }

    private function records(): array
    {
        $adminRole = Roles::create(['name' => 'Admin']);
        $masterRole = Roles::create(['name' => 'Master']);
        $admin = User::factory()->create(['role_id' => $adminRole->id, 'username' => '@cancel_admin']);
        $master = User::factory()->create(['role_id' => $masterRole->id, 'username' => '@cancel_master']);
        $otherMaster = User::factory()->create(['role_id' => $masterRole->id, 'username' => '@cancel_other']);
        $service = Services::create([
            'name' => 'Cancellation service',
            'price' => 40,
            'duration_minutes' => 60,
            'eventColor' => '#123456',
            'status' => 1,
        ]);
        $appointment = Appointments::create([
            'status' => 'confirmed',
            'user_id' => $master->id,
            'service_id' => $service->id,
            'price' => 40,
            'appointment_start' => now()->addWeek()->startOfHour(),
            'appointment_end' => now()->addWeek()->startOfHour()->addHour(),
            'client_name' => 'Cancellation test client',
            'client_lastname' => 'Example',
            'client_email' => 'cancel@example.test',
            'client_phone' => '+37250000123',
        ]);

        return [$admin, $master, $otherMaster, $appointment];
    }
}
