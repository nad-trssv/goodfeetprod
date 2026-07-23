<?php

namespace Tests\Feature;

use App\Models\Appointments;
use App\Models\RedDay;
use App\Models\Roles;
use App\Models\ServiceRule;
use App\Models\Services;
use App\Models\User;
use App\Models\UserSchedule;
use App\Services\Booking\SlotAvailabilityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AvailabilityConflictMatrixTest extends TestCase
{
    use RefreshDatabase;

    private User $master;
    private Services $service;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2030-01-01 08:00:00');
        $role = Roles::create(['name' => 'Master']);
        $this->master = User::factory()->create(['username' => '@matrix_master', 'role_id' => $role->id]);
        $this->service = Services::create([
            'name' => 'Matrix service',
            'eventColor' => '#123456',
            'price' => 50,
            'duration_minutes' => 60,
            'status' => true,
        ]);
        $this->service->users()->attach($this->master);
        UserSchedule::create([
            'user_id' => $this->master->id,
            'tuesday_start' => '09:00',
            'tuesday_end' => '18:00',
        ]);
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_service_master_date_and_working_day_guards(): void
    {
        $engine = app(SlotAvailabilityService::class);

        $this->assertNotEmpty($engine->slots('2030-01-15', $this->service->id, $this->master->id));
        $this->assertSame([], $engine->slots('2029-12-31', $this->service->id, $this->master->id), 'Past dates must be closed.');
        $this->assertSame([], $engine->slots('2030-02-15', $this->service->id, $this->master->id), 'Dates beyond the booking horizon must be closed.');
        $this->assertSame([], $engine->slots('2030-01-16', $this->service->id, $this->master->id), 'A day without working hours must be closed.');

        $other = User::factory()->create(['username' => '@matrix_other', 'role_id' => $this->master->role_id]);
        $this->assertSame([], $engine->slots('2030-01-15', $this->service->id, $other->id), 'The master must offer the service.');
        $this->service->update(['status' => false]);
        $this->assertSame([], $engine->slots('2030-01-15', $this->service->id, $this->master->id), 'Inactive services must be closed.');
    }

    public function test_lunch_partial_closures_and_appointments_use_strict_overlap_boundaries(): void
    {
        $schedule = $this->master->schedule;
        $schedule->update(['lunch_start' => '12:00', 'lunch_end' => '13:00']);
        RedDay::create([
            'name' => 'Closed interval', 'date' => '2030-01-15', 'full_day' => false,
            'start_time' => '15:00', 'end_time' => '16:00', 'user_id' => $this->master->id,
        ]);
        Appointments::create($this->appointment('2030-01-15 10:00:00', '2030-01-15 11:00:00'));

        $engine = app(SlotAvailabilityService::class);
        $this->assertFalse($engine->containsSlot('2030-01-15', $this->service->id, $this->master->id, '09:30', '10:30'));
        $this->assertTrue($engine->containsSlot('2030-01-15', $this->service->id, $this->master->id, '11:00', '12:00'), 'Touching an appointment boundary must be allowed.');
        $this->assertFalse($engine->containsSlot('2030-01-15', $this->service->id, $this->master->id, '11:30', '12:30'));
        $this->assertTrue($engine->containsSlot('2030-01-15', $this->service->id, $this->master->id, '14:00', '15:00'), 'Touching a closure boundary must be allowed.');
        $this->assertFalse($engine->containsSlot('2030-01-15', $this->service->id, $this->master->id, '14:30', '15:30'));
        $this->assertTrue($engine->containsSlot('2030-01-15', $this->service->id, $this->master->id, '16:00', '17:00'));
    }

    public function test_global_personal_and_repeating_full_day_closures_are_combined(): void
    {
        $engine = app(SlotAvailabilityService::class);
        RedDay::create(['name' => 'Global', 'date' => '2030-01-15', 'full_day' => true]);
        $this->assertSame([], $engine->slots('2030-01-15', $this->service->id, $this->master->id));

        RedDay::query()->delete();
        RedDay::create(['name' => 'Personal', 'date' => '2030-01-15', 'full_day' => true, 'user_id' => $this->master->id]);
        $this->assertSame([], $engine->slots('2030-01-15', $this->service->id, $this->master->id));

        RedDay::query()->delete();
        RedDay::create(['name' => 'Annual', 'date' => '2029-01-15', 'full_day' => true, 'repeat' => true]);
        $this->assertSame([], $engine->slots('2030-01-15', $this->service->id, $this->master->id));
    }

    public function test_service_rules_and_master_fixed_starts_determine_exact_slots(): void
    {
        ServiceRule::create([
            'service_id' => $this->service->id,
            'valid_from' => '2030-01-10',
            'valid_to' => '2030-01-20',
            'duration_minutes' => 90,
            'has_fixed_time' => true,
            'time_from' => '10:00',
            'time_to' => '12:00',
        ]);
        $engine = app(SlotAvailabilityService::class);
        $slots = collect($engine->slots('2030-01-15', $this->service->id, $this->master->id));
        $this->assertSame(['10:00', '10:30'], $slots->pluck('start')->all());
        $this->assertSame(['11:30', '12:00'], $slots->pluck('end')->all());

        $this->master->schedule->update(['fixed_booking_enabled' => true, 'fixed_booking_slots' => ['09:15', '16:45']]);
        $slots = collect($engine->slots('2030-01-15', $this->service->id, $this->master->id));
        $this->assertSame(['09:15'], $slots->pluck('start')->all());
        $this->assertSame(['10:45'], $slots->pluck('end')->all(), 'A fixed start that cannot fit before closing must be rejected.');
    }

    public function test_excluding_current_appointment_allows_safe_rescheduling_without_hiding_other_conflicts(): void
    {
        $appointment = Appointments::create($this->appointment('2030-01-15 10:00:00', '2030-01-15 11:00:00'));
        Appointments::create($this->appointment('2030-01-15 12:00:00', '2030-01-15 13:00:00'));
        $engine = app(SlotAvailabilityService::class);

        $this->assertFalse($engine->containsSlot('2030-01-15', $this->service->id, $this->master->id, '10:00', '11:00'));
        $this->assertTrue($engine->containsSlot('2030-01-15', $this->service->id, $this->master->id, '10:00', '11:00', $appointment->id));
        $this->assertFalse($engine->containsSlot('2030-01-15', $this->service->id, $this->master->id, '11:30', '12:30', $appointment->id));
    }

    private function appointment(string $start, string $end): array
    {
        return [
            'user_id' => $this->master->id,
            'service_id' => $this->service->id,
            'price' => 50,
            'appointment_start' => $start,
            'appointment_end' => $end,
            'client_name' => 'Matrix',
            'client_lastname' => 'Client',
        ];
    }
}
