<?php

namespace Tests\Feature;

use App\Mail\AppointmentCustomerMessage;
use App\Models\Appointments;
use App\Models\Roles;
use App\Models\Services;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Tests\TestCase;

class AppointmentManagementTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_today_page_only_shows_current_masters_day_and_highlights_unresolved_finished_work(): void
    {
        Carbon::setTestNow('2030-08-05 14:00:00');
        [, $master, $service] = $this->records();
        $other = User::factory()->create(['username' => '@today_other', 'role_id' => $master->role_id, 'email_verified_at' => now()]);
        $unresolved = $this->appointment($master, $service, 'confirmed', '2030-08-05 09:00:00');
        $future = $this->appointment($master, $service, 'confirmed', '2030-08-05 15:00:00');
        $foreign = $this->appointment($other, $service, 'confirmed', '2030-08-05 10:00:00');

        $this->actingAs($master)->get(route('appointments.today'))
            ->assertOk()
            ->assertSee('Нужно указать результат')
            ->assertSee('Завершившихся записей без итогового статуса: 1')
            ->assertSee(route('calendar.show', $unresolved))
            ->assertSee(route('calendar.show', $future))
            ->assertDontSee(route('calendar.show', $foreign));
    }

    public function test_super_admin_sees_the_same_complete_today_set_on_calendar_dashboard_and_today_page(): void
    {
        Carbon::setTestNow('2030-08-05 14:00:00');
        [$admin, $master, $service] = $this->records();
        $other = User::factory()->create(['username' => '@scope_other', 'role_id' => $master->role_id, 'email_verified_at' => now()]);
        $active = $this->appointment($master, $service, 'completed', '2030-08-05 09:00:00');
        $cancelled = $this->appointment($other, $service, 'cancelled_by_client', '2030-08-05 10:00:00');

        $this->actingAs($admin)->get(route('calendar.index'))->assertOk()
            ->assertSee('"id":'.$active->id, false)
            ->assertSee('"id":'.$cancelled->id, false);
        $this->actingAs($admin)->get(route('dashboard'))->assertOk()->assertSee(__('admin_dashboard.today_clients', ['count' => 2]));
        $this->actingAs($admin)->get(route('appointments.today'))->assertOk()
            ->assertSee(route('calendar.show', $active))
            ->assertSee(route('calendar.show', $cancelled))
            ->assertSee(__('appointment_statuses.cancelled_by_client'));
    }

    public function test_cancelled_finished_appointment_result_can_be_corrected_from_details_and_is_audited(): void
    {
        Carbon::setTestNow('2030-08-05 14:00:00');
        [$admin, $master, $service] = $this->records();
        $appointment = $this->appointment($master, $service, 'cancelled_by_client', '2030-08-05 09:00:00');

        $this->actingAs($admin)->get(route('calendar.show', $appointment))
            ->assertOk()->assertSee('value="completed"', false);
        $this->actingAs($admin)->patchJson(route('appointments.status.update', $appointment), [
            'status' => 'completed', 'reason' => 'Клиент всё-таки пришёл',
        ])->assertOk();

        $this->assertSame('completed', $appointment->refresh()->status);
        $this->assertDatabaseHas('appointment_audits', [
            'appointment_id' => $appointment->id, 'event' => 'status_changed',
            'actor_id' => $admin->id, 'reason' => 'Клиент всё-таки пришёл',
        ]);
    }

    public function test_master_list_contains_cancelled_appointments_and_details_show_customer_history(): void
    {
        [$admin, $master, $service] = $this->records();
        $first = $this->appointment($master, $service, 'cancelled_by_client', '2030-08-05 09:00:00');
        $second = $this->appointment($master, $service, 'completed', '2030-08-04 09:00:00');

        $this->actingAs($admin)->get(route('master.calendar.list', $master))
            ->assertOk()
            ->assertSee('#'.$first->id);

        $this->actingAs($admin)->get(route('calendar.show', $first))
            ->assertOk()
            ->assertSee('Последние записи клиента')
            ->assertSee('#'.$second->id);
    }

    public function test_authorized_master_can_email_customer_and_action_is_audited(): void
    {
        Mail::fake();
        [, $master, $service] = $this->records();
        $appointment = $this->appointment($master, $service, 'confirmed', '2030-08-05 09:00:00');

        $this->actingAs($master)->post(route('appointments.message.store', $appointment), [
            'subject' => 'Важная информация',
            'message' => 'Пожалуйста, приходите за десять минут.',
        ])->assertRedirect();

        Mail::assertSent(AppointmentCustomerMessage::class, fn ($mail) => $mail->hasTo('client@example.test'));
        $this->assertDatabaseHas('appointment_audits', [
            'appointment_id' => $appointment->id,
            'event' => 'customer_message_sent',
            'actor_id' => $master->id,
        ]);
    }

    public function test_master_cannot_email_another_masters_customer(): void
    {
        Mail::fake();
        [, $master, $service] = $this->records();
        $other = User::factory()->create(['username' => '@other_master', 'role_id' => $master->role_id, 'email_verified_at' => now()]);
        $appointment = $this->appointment($master, $service, 'confirmed', '2030-08-05 09:00:00');

        $this->actingAs($other)->post(route('appointments.message.store', $appointment), [
            'subject' => 'Важная информация', 'message' => 'Текст сообщения клиенту',
        ])->assertForbidden();
        Mail::assertNothingSent();
    }

    private function records(): array
    {
        $adminRole = Roles::create(['name' => 'Super Admin']);
        $masterRole = Roles::create(['name' => 'Master']);
        $admin = User::factory()->create(['username' => '@management_admin', 'role_id' => $adminRole->id, 'email_verified_at' => now()]);
        $master = User::factory()->create(['username' => '@management_master', 'role_id' => $masterRole->id, 'email_verified_at' => now()]);
        $service = Services::create(['name' => 'Service', 'eventColor' => '#123456', 'price' => 50, 'duration_minutes' => 60, 'status' => true]);
        return [$admin, $master, $service];
    }

    private function appointment(User $master, Services $service, string $status, string $start): Appointments
    {
        return Appointments::create([
            'client_name' => 'Ирина', 'client_lastname' => 'Иванова',
            'client_phone' => '+37255550000', 'client_email' => 'client@example.test',
            'user_id' => $master->id, 'service_id' => $service->id, 'status' => $status,
            'appointment_start' => $start, 'appointment_end' => date('Y-m-d H:i:s', strtotime($start.' +1 hour')),
            'price' => 50,
        ]);
    }
}
