<?php

namespace Tests\Feature;

use App\Models\Appointments;
use App\Models\Roles;
use App\Models\Services;
use App\Models\User;
use App\Models\UserSchedule;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAppointmentAvailabilityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2030-01-01 08:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_admin_creation_and_update_use_the_same_availability_engine_and_server_price(): void
    {
        $adminRole = Roles::create(['name' => 'Super Admin']);
        $masterRole = Roles::create(['name' => 'Master']);
        $admin = User::factory()->create(['username' => '@matrix_admin', 'role_id' => $adminRole->id, 'email_verified_at' => now()]);
        $master = User::factory()->create(['username' => '@matrix_staff', 'role_id' => $masterRole->id, 'email_verified_at' => now()]);
        UserSchedule::create([
            'user_id' => $master->id,
            'tuesday_start' => '09:00',
            'tuesday_end' => '18:00',
            'lunch_start' => '12:00',
            'lunch_end' => '13:00',
        ]);
        $service = Services::create([
            'name' => 'Admin matrix service', 'eventColor' => '#123456', 'price' => 75,
            'duration_minutes' => 60, 'status' => true,
        ]);
        $service->users()->attach($master);

        $payload = $this->payload($master, $service, '2030-01-15 10:00:00', '2030-01-15 11:00:00');
        $this->actingAs($admin)->postJson(route('calendar.store'), $payload)
            ->assertOk();
        $appointment = Appointments::firstOrFail();
        $this->assertEquals(75, $appointment->price, 'A submitted price must not override the server price.');
        $this->assertDatabaseHas('appointment_audits', [
            'appointment_id' => $appointment->id,
            'event' => 'created',
            'actor_id' => $admin->id,
        ]);

        $this->actingAs($admin)->postJson(route('calendar.store'), $this->payload($master, $service, '2030-01-15 10:30:00', '2030-01-15 11:30:00'))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('appointment_start');
        $this->actingAs($admin)->postJson(route('calendar.store'), $this->payload($master, $service, '2030-01-15 12:00:00', '2030-01-15 13:00:00'))
            ->assertUnprocessable();

        $update = $payload + ['id' => $appointment->id];
        $this->actingAs($admin)->putJson(route('calendar.update', $appointment), $update)
            ->assertOk();
        $this->assertSame('2030-01-15 10:00:00', $appointment->refresh()->appointment_start->format('Y-m-d H:i:s'));
    }

    private function payload(User $master, Services $service, string $start, string $end): array
    {
        return [
            'title' => 'Appointment',
            'client_name' => 'Test',
            'client_lastname' => 'Client',
            'client_phone' => '+37255555555',
            'service_id' => $service->id,
            'user_id' => $master->id,
            'price' => 1,
            'appointment_start' => $start,
            'appointment_end' => $end,
        ];
    }
}
