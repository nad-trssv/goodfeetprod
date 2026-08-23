<?php

namespace Tests\Feature;

use App\Models\AppointmentRescheduleRequest;
use App\Models\Appointments;
use App\Models\Customer;
use App\Models\Roles;
use App\Models\Services;
use App\Models\User;
use App\Services\Booking\AppointmentNotificationService;
use App\Services\Booking\AppointmentStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_notification_is_personal_to_super_admin_and_assigned_master(): void
    {
        [$admin, $master, $otherMaster, $appointment] = $this->records();

        app(AppointmentNotificationService::class)->send($appointment, 'booking_created');

        $this->assertCount(1, $admin->notifications);
        $this->assertCount(1, $master->notifications);
        $this->assertCount(0, $otherMaster->notifications);
        $this->assertSame('booking_created', $master->notifications->first()->data['event']);

        $this->actingAs($master)->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('Notification client')
            ->assertSee('Notification service');

        $this->actingAs($master)->getJson(route('notifications.status'))
            ->assertOk()
            ->assertJsonPath('count', 1)
            ->assertJsonPath('html', fn ($html) => str_contains($html, 'Notification client'));

        $notification = $master->notifications->first();
        $this->actingAs($master)->post(route('notifications.read', $notification->id))
            ->assertRedirect(route('calendar.show', $appointment));
        $this->assertNotNull($notification->fresh()->read_at);
        $this->assertNull($admin->notifications->first()->fresh()->read_at);
    }

    public function test_cancellation_creates_a_new_notification(): void
    {
        [$admin, $master, , $appointment] = $this->records();

        app(AppointmentStatusService::class)->transition($appointment, 'cancelled_by_client');

        $this->assertSame('cancelled_by_client', $admin->notifications()->first()->data['event']);
        $this->assertSame('cancelled_by_client', $master->notifications()->first()->data['event']);
    }

    public function test_admin_layout_contains_a_local_notification_sound_for_new_counts(): void
    {
        [, $master] = $this->records();

        $this->actingAs($master)->get(route('notifications.index'))
            ->assertOk()
            ->assertSee('window.adminNotificationSound', false)
            ->assertSee('window.AudioContext', false)
            ->assertSee('Number(data.count) > previousCount', false);
    }

    public function test_opening_appointment_directly_marks_its_notifications_as_read(): void
    {
        [, $master, , $appointment] = $this->records();
        app(AppointmentNotificationService::class)->send($appointment, 'booking_created');
        $notification = $master->notifications()->firstOrFail();

        $this->actingAs($master)->get(route('calendar.show',$appointment))->assertOk();

        $this->assertNotNull($notification->fresh()->read_at);
        $this->assertSame(0,$master->fresh()->unreadNotifications()->count());
    }

    public function test_reschedule_page_keeps_processed_request_history_for_the_master(): void
    {
        [, $master, , $appointment] = $this->records();
        AppointmentRescheduleRequest::create([
            'appointment_id' => $appointment->id,
            'customer_id' => $appointment->customer_id,
            'user_id' => $master->id,
            'service_id' => $appointment->service_id,
            'old_start' => $appointment->appointment_start,
            'old_end' => $appointment->appointment_end,
            'requested_start' => $appointment->appointment_start->copy()->addDay(),
            'requested_end' => $appointment->appointment_end->copy()->addDay(),
            'status' => 'approved',
            'reviewed_by' => $master->id,
            'reviewed_at' => now(),
        ]);

        $this->actingAs($master)->get(route('reschedule.index'))
            ->assertOk()
            ->assertSee('Notification client')
            ->assertSee(__('admin_notifications.reschedule_history'))
            ->assertSee(__('admin_notifications.statuses.approved'));
    }

    private function records(): array
    {
        $adminRole = Roles::create(['name' => 'Admin']);
        $masterRole = Roles::create(['name' => 'Master']);
        $admin = User::factory()->create(['username' => '@notify_admin', 'role_id' => $adminRole->id, 'email_verified_at' => now()]);
        $master = User::factory()->create(['username' => '@notify_master', 'role_id' => $masterRole->id, 'email_verified_at' => now()]);
        $otherMaster = User::factory()->create(['username' => '@notify_other', 'role_id' => $masterRole->id, 'email_verified_at' => now()]);
        $service = Services::create(['name' => 'Notification service', 'price' => 50, 'duration_minutes' => 60, 'eventColor' => '#123456', 'status' => 1]);
        $customer = Customer::create(['first_name' => 'Notification', 'last_name' => 'client', 'email' => 'notify@example.test', 'phone' => '+37250000999']);
        $appointment = Appointments::create([
            'customer_id' => $customer->id, 'status' => 'confirmed', 'user_id' => $master->id, 'service_id' => $service->id,
            'price' => 50, 'appointment_start' => now()->addDays(5)->startOfHour(), 'appointment_end' => now()->addDays(5)->startOfHour()->addHour(),
            'client_name' => 'Notification', 'client_lastname' => 'client', 'client_email' => $customer->email, 'client_phone' => $customer->phone,
        ]);

        return [$admin, $master, $otherMaster, $appointment];
    }
}
