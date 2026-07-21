<?php

namespace Tests\Feature;

use App\Models\Appointments;
use App\Models\Roles;
use App\Models\Services;
use App\Models\SiteSettings;
use App\Models\User;
use App\Services\DashboardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardActivityTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrator_activity_contains_all_masters_clients_for_today(): void
    {
        $adminRole = Roles::create(['name' => 'Admin']);
        $masterRole = Roles::create(['name' => 'Master']);
        $admin = User::factory()->create(['username' => '@dashboard_admin', 'role_id' => $adminRole->id]);
        $first = User::factory()->create(['username' => '@dashboard_first', 'role_id' => $masterRole->id]);
        $second = User::factory()->create(['username' => '@dashboard_second', 'role_id' => $masterRole->id]);
        $service = Services::create([
            'name' => 'Dashboard service',
            'eventColor' => '#112233',
            'price' => 50,
            'duration_minutes' => 60,
            'status' => true,
        ]);
        SiteSettings::create([
            'group' => 'hours',
            'key' => 'work_hours',
            'payload' => json_encode([
                strtolower(now()->englishDayOfWeek) => ['start' => '09:00', 'end' => '18:00'],
            ]),
        ]);

        foreach ([$first, $second] as $index => $master) {
            Appointments::create([
                'user_id' => $master->id,
                'service_id' => $service->id,
                'price' => 50,
                'appointment_start' => today()->setTime(10 + $index, 0),
                'appointment_end' => today()->setTime(11 + $index, 0),
                'client_name' => 'Today',
                'client_lastname' => 'Client '.($index + 1),
            ]);
        }

        $this->actingAs($admin);
        $data = app(DashboardService::class)->index();

        $this->assertCount(2, $data['appointments']);
        $this->assertEqualsCanonicalizing([$first->id, $second->id], collect($data['appointments'])->pluck('user_id')->all());
    }
}
