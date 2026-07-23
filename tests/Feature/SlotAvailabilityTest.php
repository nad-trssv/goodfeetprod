<?php

namespace Tests\Feature;

use App\Models\Appointments;
use App\Models\RedDay;
use App\Models\Roles;
use App\Models\Room;
use App\Models\Services;
use App\Models\SiteSettings;
use App\Models\User;
use App\Models\UserSchedule;
use App\Services\Booking\SlotAvailabilityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;
use Carbon\Carbon;

class SlotAvailabilityTest extends TestCase
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

    public function test_slots_respect_schedule_lunch_closures_bookings_and_room_capacity(): void
    {
        $role = Roles::create(['name' => 'Master']);
        $master = User::factory()->create(['username' => '@slot_master', 'role_id' => $role->id]);
        $colleague = User::factory()->create(['username' => '@slot_colleague', 'role_id' => $role->id]);
        foreach ([$master, $colleague] as $user) {
            UserSchedule::create([
                'user_id' => $user->id,
                'tuesday_start' => '09:00',
                'tuesday_end' => '18:00',
                'lunch_start' => '12:00',
                'lunch_end' => '13:00',
            ]);
        }
        $service = Services::create([
            'name' => 'Availability service',
            'eventColor' => '#123456',
            'price' => 40,
            'duration_minutes' => 60,
            'status' => true,
        ]);
        $service->users()->attach($master);
        SiteSettings::create([
            'group' => 'hours',
            'key' => 'fixed_booking_hours',
            'payload' => json_encode(['value' => ['false'], 'payload' => ['09:00']]),
        ]);
        $room = Room::create(['name' => 'Test room', 'capacity' => 1, 'is_active' => true]);
        $room->users()->attach([$master->id, $colleague->id]);

        Appointments::create($this->appointment($master, $service, $room, '2030-01-15 10:00:00', '2030-01-15 11:00:00'));
        Appointments::create($this->appointment($colleague, $service, $room, '2030-01-15 14:00:00', '2030-01-15 15:00:00'));
        RedDay::create([
            'user_id' => $master->id,
            'name' => 'Closed hour',
            'date' => '2030-01-15',
            'start_time' => '16:00',
            'end_time' => '17:00',
            'full_day' => false,
            'repeat' => false,
        ]);

        DB::flushQueryLog();
        DB::enableQueryLog();
        $starts = collect(app(SlotAvailabilityService::class)->slots('2030-01-15', $service->id, $master->id))->pluck('start');
        $queryCount = count(DB::getQueryLog());
        DB::disableQueryLog();

        $this->assertTrue($starts->contains('09:00'));
        $this->assertTrue($starts->contains('11:00'));
        $this->assertFalse($starts->contains('09:30'));
        $this->assertFalse($starts->contains('10:00'));
        $this->assertFalse($starts->contains('10:30'));
        $this->assertFalse($starts->contains('11:30'));
        $this->assertFalse($starts->contains('12:00'));
        $this->assertFalse($starts->contains('14:00'));
        $this->assertFalse($starts->contains('16:00'));
        $this->assertFalse($starts->contains('17:30'));
        $this->assertLessThanOrEqual(20, $queryCount, 'A one-day slot lookup must not execute queries per candidate time.');
    }

    private function appointment(User $user, Services $service, Room $room, string $start, string $end): array
    {
        return [
            'user_id' => $user->id,
            'service_id' => $service->id,
            'room_id' => $room->id,
            'price' => 40,
            'appointment_start' => $start,
            'appointment_end' => $end,
            'client_name' => 'Test',
            'client_lastname' => 'Client',
        ];
    }
}
