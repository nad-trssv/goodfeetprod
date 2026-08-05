<?php

namespace Tests\Feature;

use App\Models\Appointments;
use App\Models\RedDay;
use App\Models\Roles;
use App\Models\Services;
use App\Models\User;
use App\Models\UserSchedule;
use Carbon\Carbon;
use App\Services\Booking\BookingCalendarService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EmployeeCardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Carbon::setTestNow('2030-07-15 12:00:00');
    }

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_employee_edit_is_a_unified_card_with_statistics_services_and_calendar(): void
    {
        [$admin, $member, $service] = $this->records();

        $this->actingAs($admin)->get(route('member.edit', $member))
            ->assertOk()
            ->assertSee('Основное')
            ->assertSee('Статистика')
            ->assertSee('Рабочий календарь')
            ->assertSee('Выручка за текущий месяц')
            ->assertSee('100,00 €')
            ->assertSee('2 года 2 месяца 5 дней')
            ->assertSee($service->name)
            ->assertSee('services/employee-card.webp')
            ->assertSee('Отпуск');

        $this->actingAs($admin)->get(route('admin.master-services.index', ['master_id' => $member->id]))
            ->assertOk()
            ->assertSee($service->name)
            ->assertSee(route('admin.master-services.toggle', [$member->id, $service->id]), false);
    }

    public function test_updating_main_profile_does_not_sync_services_or_overwrite_schedule(): void
    {
        [$admin, $member, $service] = $this->records();
        $scheduleBefore = $member->schedule->only(['monday_start', 'monday_end', 'lunch_start', 'lunch_end']);

        $this->actingAs($admin)->putJson(route('member.update', $member), [
            'name' => 'Updated employee',
            'username' => '@employee',
            'email' => 'employee@example.test',
            'phone' => '+37250000111',
            'role_id' => $member->role_id,
            'date_birthday' => '1990-01-01',
            'employment_started_at' => '2028-05-10',
            'services' => [],
            'monday_start' => null,
            'monday_end' => null,
        ])->assertOk();

        $member->refresh();
        $this->assertSame('Updated employee', $member->name);
        $this->assertSame('2028-05-10', $member->employment_started_at->format('Y-m-d'));
        $this->assertTrue($member->services()->whereKey($service->id)->exists());
        $this->assertSame($scheduleBefore, $member->schedule->fresh()->only(array_keys($scheduleBefore)));
    }

    public function test_shared_service_list_keeps_toggle_settings_and_activity_logging(): void
    {
        [$admin, $member, $service] = $this->records();

        $this->actingAs($admin)->post(route('admin.master-services.toggle', [$member->id, $service->id]))
            ->assertRedirect();
        $this->assertDatabaseHas('user_services', ['user_id' => $member->id, 'service_id' => $service->id, 'is_active' => false]);
        $this->assertDatabaseHas('activity_logs', ['actor_id' => $admin->id, 'event' => 'master_service.disabled']);

        $this->actingAs($admin)->put(route('admin.master-services.update', [$member->id, $service->id]), [
            'service_form_id' => $service->id,
            'use_default_price' => 0,
            'price_override' => 125,
            'use_default_duration' => 1,
            'duration_minutes_override' => null,
            'use_default_min_duration' => 1,
            'duration_minutes_min_override' => null,
            'buffer_before_minutes' => 10,
            'buffer_after_minutes' => 15,
        ])->assertRedirect();

        $this->assertDatabaseHas('user_services', ['user_id' => $member->id, 'service_id' => $service->id, 'price_override' => 125, 'buffer_before_minutes' => 10, 'buffer_after_minutes' => 15]);
        $this->assertDatabaseHas('activity_logs', ['actor_id' => $admin->id, 'event' => 'master_service.settings_updated']);
    }

    public function test_admin_can_edit_weekly_schedule_and_manage_employee_closures_calendar(): void
    {
        [$admin, $member] = $this->records();
        $schedule = [];
        foreach (['monday','tuesday','wednesday','thursday','friday','saturday','sunday'] as $day) {
            $schedule[$day.'_off'] = in_array($day, ['saturday','sunday'], true) ? 1 : 0;
            if (!$schedule[$day.'_off']) { $schedule[$day.'_start']='08:30'; $schedule[$day.'_end']='17:30'; }
        }
        $schedule['lunch_start']='12:00'; $schedule['lunch_end']='12:45';
        $this->actingAs($admin)->put(route('member.schedule.update',$member),$schedule)->assertRedirect();
        $this->assertDatabaseHas('user_schedules',['user_id'=>$member->id,'monday_start'=>'08:30','sunday_start'=>null,'lunch_end'=>'12:45']);

        $this->actingAs($admin)->post(route('member.closures.store',$member),['name'=>'Training','type'=>'training','date'=>'2030-07-25','date_to'=>'2030-07-27','full_day'=>0,'start_time'=>'14:00','end_time'=>'16:00','repeat'=>0])->assertRedirect();
        $closure=RedDay::where('name','Training')->firstOrFail();
        $events = $this->getJson(route('member.closures.events',[$member,'start'=>'2030-07-01','end'=>'2030-08-01']))->assertOk()->json();
        $this->assertCount(3, collect($events)->where('id', (string) $closure->id));
        $this->assertCount(1, app(BookingCalendarService::class)->partialClosures('2030-07-26',$member->id));
        $this->actingAs($admin)->put(route('member.closures.update',[$member,$closure]),['name'=>'Training updated','type'=>'paid_vacation','date'=>'2030-07-26','date_to'=>'2030-07-29','full_day'=>1,'repeat'=>0])->assertRedirect();
        $this->assertDatabaseHas('red_days',['id'=>$closure->id,'name'=>'Training updated','type'=>'paid_vacation','date_to'=>'2030-07-29','full_day'=>true,'start_time'=>null]);
        $this->assertTrue(app(BookingCalendarService::class)->isClosed('2030-07-28',$member->id));
        $this->actingAs($admin)->delete(route('member.closures.destroy',[$member,$closure]))->assertRedirect();
        $this->assertDatabaseMissing('red_days',['id'=>$closure->id]);
    }

    public function test_tenure_omits_zero_years_and_months(): void
    {
        [$admin, $member] = $this->records();
        $member->update(['employment_started_at' => now()->subDays(15)->toDateString()]);
        $this->actingAs($admin)->get(route('member.edit', $member))
            ->assertOk()->assertSee('15 дней')->assertDontSee('0 лет')->assertDontSee('0 месяцев');
    }

    public function test_member_overview_shows_operational_status_without_service_badge_list(): void
    {
        [$admin, $member, $service] = $this->records();
        RedDay::where('user_id', $member->id)->delete();
        $member->schedule->update(['lunch_start' => null, 'lunch_end' => null]);
        $member->update(['last_active' => now()->subMinute()]);
        Appointments::create([
            'user_id' => $member->id,
            'service_id' => $service->id,
            'status' => 'in_progress',
            'price' => 100,
            'appointment_start' => now()->subMinutes(30),
            'appointment_end' => now()->addMinutes(30),
            'client_name' => 'Current',
            'client_lastname' => 'Client',
        ]);

        $this->actingAs($admin)->get(route('member.index'))
            ->assertOk()
            ->assertSee('Сейчас с клиентом')
            ->assertSee('Сейчас онлайн')
            ->assertSee('1 активных')
            ->assertSee('Current Client')
            ->assertSee('member-card__avatar-fallback', false)
            ->assertDontSee('bg-secondary fs-10 me-1', false);
    }

    public function test_master_and_admin_can_resolve_finished_appointments_and_today_totals_are_calculated(): void
    {
        [$admin, $member, $service] = $this->records();
        $finished = Appointments::create([
            'user_id' => $member->id, 'service_id' => $service->id, 'status' => 'confirmed', 'price' => 80,
            'appointment_start' => now()->subHours(2), 'appointment_end' => now()->subHour(),
            'client_name' => 'Finished', 'client_lastname' => 'Client',
        ]);

        $this->actingAs($member)->patchJson(route('appointments.status.update', $finished), ['status' => 'completed'])
            ->assertOk()->assertJsonPath('status', 'completed');
        $this->assertDatabaseHas('appointment_status_histories', ['appointment_id' => $finished->id, 'to_status' => 'completed', 'changed_by_id' => $member->id]);
        $this->actingAs($admin)->patchJson(route('appointments.status.update', $finished), ['status' => 'no_show'])
            ->assertUnprocessable()->assertJsonValidationErrors('reason');
        $this->actingAs($admin)->patchJson(route('appointments.status.update', $finished), ['status' => 'no_show', 'reason' => 'Marked incorrectly'])
            ->assertOk()->assertJsonPath('status', 'no_show');
        $this->assertDatabaseHas('appointment_status_histories', ['appointment_id'=>$finished->id,'from_status'=>'completed','to_status'=>'no_show','changed_by_id'=>$admin->id,'reason'=>'Marked incorrectly']);
        $this->actingAs($admin)->patchJson(route('appointments.status.update', $finished), ['status' => 'completed', 'reason' => 'Customer did attend'])
            ->assertOk()->assertJsonPath('status', 'completed');

        $noShow = Appointments::create([
            'user_id' => $member->id, 'service_id' => $service->id, 'status' => 'confirmed', 'price' => 65,
            'appointment_start' => now()->subHours(3), 'appointment_end' => now()->subHours(2),
            'client_name' => 'Missing', 'client_lastname' => 'Client',
        ]);
        $this->actingAs($admin)->patchJson(route('appointments.status.update', $noShow), ['status' => 'no_show'])->assertOk();

        $this->actingAs($admin)->get(route('member.index'))->assertOk()
            ->assertSee('80 €')
            ->assertSee('65 €')
            ->assertSee('неявки')
            ->assertSee('потеряно');
    }

    public function test_master_cannot_change_another_masters_or_future_appointment_status(): void
    {
        [$admin, $member, $service] = $this->records();
        $other = User::factory()->create(['role_id' => $member->role_id, 'username' => '@other-master']);
        $past = Appointments::create(['user_id'=>$other->id,'service_id'=>$service->id,'status'=>'confirmed','price'=>50,'appointment_start'=>now()->subHours(2),'appointment_end'=>now()->subHour(),'client_name'=>'Other','client_lastname'=>'Client']);
        $future = Appointments::create(['user_id'=>$member->id,'service_id'=>$service->id,'status'=>'confirmed','price'=>50,'appointment_start'=>now()->addHour(),'appointment_end'=>now()->addHours(2),'client_name'=>'Future','client_lastname'=>'Client']);

        $this->actingAs($member)->patchJson(route('appointments.status.update',$past),['status'=>'completed'])->assertForbidden();
        $this->actingAs($member)->patchJson(route('appointments.status.update',$future),['status'=>'completed'])->assertUnprocessable();
        $this->assertSame('confirmed',$past->fresh()->status);
        $this->assertSame('confirmed',$future->fresh()->status);
    }

    private function records(): array
    {
        $adminRole = Roles::create(['name' => 'Admin']);
        $masterRole = Roles::create(['name' => 'Master']);
        $admin = User::factory()->create(['role_id' => $adminRole->id, 'username' => '@admin']);
        $member = User::factory()->create([
            'role_id' => $masterRole->id,
            'username' => '@employee',
            'email' => 'employee@example.test',
            'phone' => '+37250000111',
            'employment_started_at' => '2028-05-10',
        ]);
        $service = Services::create(['name' => 'Employee card service', 'image_path' => 'services/employee-card.webp', 'price' => 100, 'duration_minutes' => 60, 'eventColor' => '#123456', 'status' => 1]);
        $member->allServices()->attach($service->id, ['is_active' => true]);
        UserSchedule::create(['user_id' => $member->id, 'monday_start' => '09:00', 'monday_end' => '18:00', 'lunch_start' => '12:00', 'lunch_end' => '13:00']);
        RedDay::create(['user_id' => $member->id, 'name' => 'Отпуск', 'type' => 'paid_vacation', 'date' => '2030-07-14', 'date_to' => '2030-07-20', 'full_day' => true]);
        Appointments::create(['user_id' => $member->id, 'service_id' => $service->id, 'status' => 'completed', 'price' => 100, 'appointment_start' => '2030-07-10 10:00', 'appointment_end' => '2030-07-10 11:00', 'client_name' => 'Client', 'client_lastname' => 'One']);
        Appointments::create(['user_id' => $member->id, 'service_id' => $service->id, 'status' => 'cancelled_by_client', 'price' => 100, 'appointment_start' => '2030-07-11 10:00', 'appointment_end' => '2030-07-11 11:00', 'client_name' => 'Client', 'client_lastname' => 'Two']);

        return [$admin, $member, $service];
    }
}
