<?php

namespace Tests\Feature;

use App\Models\Appointments;
use App\Models\Roles;
use App\Models\Services;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCalendarVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_calendar_and_list_include_masters_appointments(): void
    {
        $adminRole = Roles::create(['name' => 'Admin']);
        $masterRole = Roles::create(['name' => 'Master']);
        $admin = User::factory()->create(['username' => '@calendar_admin', 'role_id' => $adminRole->id, 'email_verified_at' => now()]);
        $master = User::factory()->create(['username' => '@calendar_master', 'role_id' => $masterRole->id, 'email_verified_at' => now()]);
        $service = Services::create([
            'name' => 'Calendar service',
            'eventColor' => '#336699',
            'price' => 50,
            'duration_minutes' => 60,
            'status' => 1,
        ]);
        $appointment = Appointments::create([
            'client_name' => 'Calendar',
            'client_lastname' => 'Client',
            'client_phone' => '+3725550000',
            'client_email' => 'calendar@example.test',
            'service_id' => $service->id,
            'user_id' => $master->id,
            'price' => 50,
            'appointment_start' => now()->addDay()->setTime(10, 0),
            'appointment_end' => now()->addDay()->setTime(11, 0),
        ]);

        $this->actingAs($admin)->get(route('calendar.index'))
            ->assertOk()
            ->assertViewHas('appointments', function (array $items) use ($appointment) {
                $event = collect($items)->firstWhere('id', $appointment->id);
                return $event && $event['start'] === $appointment->appointment_start->format('Y-m-d\TH:i:s')
                    && ! str_ends_with($event['start'], 'Z');
            });

        $this->actingAs($admin)->get(route('calendarList'))
            ->assertOk()
            ->assertViewHas('appointments', fn (array $items) => collect($items)->pluck('id')->contains($appointment->id));

        $this->actingAs($admin)->get(route('master.calendar', $master->id))
            ->assertOk()
            ->assertViewHas('appointments', fn (array $items) => collect($items)->pluck('id')->contains($appointment->id))
            ->assertSee($master->name);

        $this->actingAs($admin)->get(route('master.calendar', $admin->id))
            ->assertOk()
            ->assertSee('0 записей')
            ->assertSee('пока нет записей');
    }
}
