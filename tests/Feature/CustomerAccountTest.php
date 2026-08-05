<?php

namespace Tests\Feature;

use App\Models\Appointments;
use App\Models\AppointmentMedia;
use App\Models\Customer;
use App\Models\Roles;
use App\Models\Services;
use App\Models\SiteSettings;
use App\Models\User;
use App\Models\UserSchedule;
use App\Services\Booking\AppointmentRescheduleService;
use App\Services\Booking\SlotAvailabilityService;
use Carbon\Carbon;
use App\Services\Booking\AppointmentStatusService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CustomerAccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_register_and_login_with_email_or_normalized_phone(): void
    {
        $payload = [
            'first_name' => 'Anna', 'last_name' => 'Client',
            'email' => 'Anna@Example.COM', 'phone' => '+372 5555 1111',
            'password' => 'secret-pass', 'password_confirmation' => 'secret-pass', 'terms' => '1',
        ];
        $this->post(route('customer.register.store'), $payload)->assertRedirect(route('customer.dashboard'));
        $customer = Customer::firstOrFail();
        $this->assertSame('anna@example.com', $customer->email);
        $this->assertSame('+37255551111', $customer->phone);
        $this->assertTrue(Hash::check('secret-pass', $customer->password));

        $this->post(route('customer.logout'))->assertRedirect(route('home'));
        $this->post(route('customer.login.store'), ['identity' => 'ANNA@example.com', 'password' => 'secret-pass'])
            ->assertRedirect(route('customer.dashboard'));
        $this->post(route('customer.logout'));
        $this->post(route('customer.login.store'), ['identity' => '+372 5555 1111', 'password' => 'secret-pass'])
            ->assertRedirect(route('customer.dashboard'));
    }

    public function test_registration_merges_passwordless_guest_identities_and_links_email_history(): void
    {
        [$master, $service] = $this->bookingEntities();
        $byEmail = Customer::create(['first_name' => 'Guest', 'email' => 'guest@example.com', 'phone' => '+37250000001']);
        $byPhone = Customer::create(['first_name' => 'Other', 'email' => 'old@example.com', 'phone' => '+37250000002']);
        $phoneAppointment = $this->appointment($byPhone, $master, $service, 'Phone history');
        $unlinked = $this->appointment($byEmail, $master, $service, 'Email history');
        $unlinked->update(['customer_id' => null, 'client_email' => ' Guest@Example.com ']);

        $this->post(route('customer.register.store'), [
            'first_name' => 'Guest', 'email' => 'guest@example.com', 'phone' => '+37250000002',
            'password' => 'secret-pass', 'password_confirmation' => 'secret-pass', 'terms' => '1',
        ])->assertRedirect(route('customer.dashboard'));

        $customer = Customer::where('email', 'guest@example.com')->firstOrFail();
        $this->assertSame('+37250000002', $customer->phone);
        $this->assertNotNull($customer->password);
        $this->assertSame(1, Customer::count());
        $this->assertSame($customer->id, $phoneAppointment->refresh()->customer_id);
        $this->assertSame($customer->id, $unlinked->refresh()->customer_id);
    }

    public function test_registration_cannot_claim_contact_from_an_existing_account(): void
    {
        Customer::create([
            'first_name' => 'Account', 'email' => 'account@example.com',
            'phone' => '+37250000003', 'password' => 'existing-pass',
        ]);

        $this->from(route('customer.register'))->post(route('customer.register.store'), [
            'first_name' => 'Conflict', 'email' => 'account@example.com', 'phone' => '+37250000009',
            'password' => 'secret-pass', 'password_confirmation' => 'secret-pass', 'terms' => '1',
        ])->assertRedirect(route('customer.register'))->assertSessionHasErrors('email');
    }

    public function test_customer_dashboard_only_contains_own_appointments_and_status_history_is_recorded(): void
    {
        [$master, $service] = $this->bookingEntities();
        $customer = Customer::create(['first_name' => 'Own', 'email' => 'own@example.com', 'phone' => '+37250000003', 'password' => 'secret-pass']);
        $other = Customer::create(['first_name' => 'Other', 'email' => 'other@example.com', 'phone' => '+37250000004', 'password' => 'secret-pass']);
        $otherService = Services::create(['name' => 'Other secret service', 'price' => 60, 'duration_minutes' => 60, 'eventColor' => '#654321', 'status' => true]);
        $ownAppointment = $this->appointment($customer, $master, $service, 'Own private client');
        $otherAppointment = $this->appointment($other, $master, $otherService, 'Other private client');
        $ownAppointment->update(['status' => 'completed', 'status_changed_at' => now()]);

        $response = $this->actingAs($customer, 'customer')->get(route('customer.dashboard'));
        $response->assertOk()->assertSee($ownAppointment->public_uuid)->assertDontSee($otherAppointment->public_uuid);
        $this->assertSame(2, $ownAppointment->statusHistory()->count());
        $this->assertDatabaseHas('appointment_status_histories', ['appointment_id' => $ownAppointment->id, 'from_status' => 'confirmed', 'to_status' => 'completed']);
        $this->assertNotSame($ownAppointment->customer_id, $otherAppointment->customer_id);
    }

    public function test_status_service_allows_valid_transition_and_rejects_transition_from_terminal_status(): void
    {
        [$master, $service] = $this->bookingEntities();
        $customer = Customer::create(['first_name' => 'Status', 'email' => 'status@example.com', 'phone' => '+37250000005']);
        $appointment = $this->appointment($customer, $master, $service, 'Status client');
        $statuses = app(AppointmentStatusService::class);

        $statuses->transition($appointment, 'checked_in', $customer, 'Customer arrived');
        $statuses->transition($appointment, 'completed', $customer);

        $this->assertSame('completed', $appointment->refresh()->status);
        $this->assertDatabaseHas('appointment_status_histories', [
            'appointment_id' => $appointment->id,
            'to_status' => 'checked_in',
            'changed_by_type' => $customer->getMorphClass(),
            'changed_by_id' => $customer->id,
            'reason' => 'Customer arrived',
        ]);

        $this->expectException(ValidationException::class);
        $statuses->transition($appointment, 'confirmed');
    }

    public function test_history_is_paginated_by_twenty_records_and_can_be_searched_filtered_and_sorted(): void
    {
        [$master, $service] = $this->bookingEntities();
        $customer = Customer::create(['first_name' => 'History', 'email' => 'history@example.com', 'phone' => '+37250000006', 'password' => 'secret-pass']);

        for ($day = 1; $day <= 55; $day++) {
            $start = now()->subDays($day)->setTime(10, 0);
            Appointments::create([
                'customer_id' => $customer->id, 'user_id' => $master->id, 'service_id' => $service->id,
                'client_name' => 'History', 'client_lastname' => 'Client',
                'client_email' => $customer->email, 'client_phone' => $customer->phone,
                'price' => 50, 'appointment_start' => $start, 'appointment_end' => $start->copy()->addHour(),
            ]);
        }

        $response = $this->actingAs($customer, 'customer')->get(route('customer.dashboard'));
        $response->assertOk();
        $this->assertSame(20, substr_count($response->getContent(), 'data-appointment='));
        $response->assertSee(__('customer.records_found', ['count' => 55]))->assertSee('page=2', false);
        $thirdPage = $this->get(route('customer.dashboard', ['page' => 3]));
        $thirdPage->assertOk();
        $this->assertSame(15, substr_count($thirdPage->getContent(), 'data-appointment='));

        $this->get(route('customer.dashboard', ['search' => 'Customer service']))
            ->assertOk()->assertSee('Customer service');
        $this->get(route('customer.dashboard', ['service_id' => $service->id, 'master_id' => $master->id, 'sort' => 'date', 'direction' => 'asc']))
            ->assertOk()->assertSee('Customer service')->assertSee($master->name);
        $this->get(route('customer.dashboard', ['search' => 'service-that-does-not-exist']))
            ->assertOk()->assertSee(__('customer.no_results'));
    }

    public function test_account_deletion_requires_password_and_anonymizes_booking_and_removes_media(): void
    {
        Storage::fake('public');
        [$master, $service] = $this->bookingEntities();
        $customer = Customer::create([
            'first_name' => 'Delete', 'last_name' => 'Me', 'email' => 'delete@example.com',
            'phone' => '+37250000007', 'password' => 'correct-pass',
        ]);
        $appointment = $this->appointment($customer, $master, $service, 'Private name');
        $appointment->update(['description' => 'Private note', 'title' => 'Private title']);
        Storage::disk('public')->put('appointments/private.jpg', 'private');
        AppointmentMedia::create(['appointment_id' => $appointment->id, 'photo_path' => 'appointments/private.jpg']);

        $this->actingAs($customer, 'customer')->from(route('customer.dashboard'))->delete(route('customer.destroy'), [
            'password' => 'wrong-pass', 'confirm_delete' => '1',
        ])->assertRedirect(route('customer.dashboard'))->assertSessionHasErrors('password');
        $this->assertDatabaseHas('customers', ['id' => $customer->id]);

        $this->delete(route('customer.destroy'), [
            'password' => 'correct-pass', 'confirm_delete' => '1',
        ])->assertRedirect(route('home'));

        $this->assertGuest('customer');
        $this->assertDatabaseMissing('customers', ['id' => $customer->id]);
        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id, 'customer_id' => null, 'client_name' => 'Deleted client',
            'client_lastname' => '', 'client_email' => null, 'client_phone' => null,
            'description' => null, 'title' => null,
        ]);
        $this->assertDatabaseMissing('appointment_media', ['appointment_id' => $appointment->id]);
        Storage::disk('public')->assertMissing('appointments/private.jpg');
    }

    public function test_profile_update_requires_password_and_does_not_rewrite_booking_snapshot(): void
    {
        [$master, $service] = $this->bookingEntities();
        $customer = Customer::create([
            'first_name' => 'Old', 'last_name' => 'Name', 'email' => 'old-profile@example.com',
            'phone' => '+37250000008', 'password' => 'correct-pass',
        ]);
        $appointment = $this->appointment($customer, $master, $service, 'Old');

        $this->actingAs($customer, 'customer')->from(route('customer.profile'))->put(route('customer.profile.update'), [
            'first_name' => 'Wrong', 'last_name' => 'Password', 'email' => 'wrong@example.com',
            'phone' => '+37250000009', 'password' => 'wrong-pass',
        ])->assertRedirect(route('customer.profile'))->assertSessionHasErrors('password');
        $this->assertSame('old-profile@example.com', $customer->refresh()->email);

        $this->put(route('customer.profile.update'), [
            'first_name' => 'New', 'last_name' => 'Profile', 'email' => 'NEW-PROFILE@example.com',
            'phone' => '+372 5000 0010', 'password' => 'correct-pass',
        ])->assertRedirect()->assertSessionHas('status');

        $this->assertSame('new-profile@example.com', $customer->refresh()->email);
        $this->assertSame('+37250000010', $customer->phone);
        $this->assertSame('old-profile@example.com', $appointment->refresh()->client_email);
        $this->assertSame('+37250000008', $appointment->client_phone);
        $this->assertSame($customer->id, $appointment->customer_id);
    }

    public function test_account_menu_and_booking_form_use_authenticated_customer_data(): void
    {
        [$master, $service] = $this->bookingEntities();
        $customer = Customer::create([
            'first_name' => 'Prefilled', 'last_name' => 'Customer', 'email' => 'prefilled@example.com',
            'phone' => '+37250000011', 'password' => 'correct-pass',
        ]);
        $this->actingAs($customer, 'customer');

        $this->get(route('customer.dashboard'))->assertOk()
            ->assertSee(route('customer.profile'))->assertSee(__('customer.booking_history'));
        $this->view('pages.booking.create', [
            'service' => [
                'id' => $service->id, 'name' => $service->name,
                'effective_price' => $service->price, 'price_can_change' => false,
                'effective_duration_minutes' => 60,
            ],
            'bookingData' => ['user_id' => $master->id, 'choose_date' => now()->addDay()->toDateString(), 'start' => '10:00', 'end' => '11:00'],
            'choose_date' => now()->addDay()->format('d.m.Y'),
            'settings' => ['company_address' => 'Test address'],
            'master' => $master,
        ])->assertSee('value="Prefilled"', false)
            ->assertSee('value="Customer"', false)
            ->assertSee('value="prefilled@example.com"', false)
            ->assertSee('value="+37250000011"', false);
    }

    public function test_customer_cancellation_respects_configurable_notice_and_ownership(): void
    {
        [$master, $service] = $this->bookingEntities();
        $customer = Customer::create(['first_name' => 'Cancel', 'email' => 'cancel@example.com', 'phone' => '+37250000012', 'password' => 'secret-pass']);
        $other = Customer::create(['first_name' => 'Other', 'email' => 'cancel-other@example.com', 'phone' => '+37250000013', 'password' => 'secret-pass']);
        $appointment = $this->appointment($customer, $master, $service, 'Cancel client');
        $appointment->update(['appointment_start' => now()->addHours(48), 'appointment_end' => now()->addHours(49)]);
        SiteSettings::create(['group' => 'booking', 'key' => 'cancellation_notice_hours', 'payload' => json_encode(24)]);

        $this->actingAs($other, 'customer')->delete(route('customer.appointments.destroy', ['appointment' => $appointment->public_uuid]))->assertNotFound();
        $this->actingAs($customer, 'customer')->delete(route('customer.appointments.destroy', ['appointment' => $appointment->public_uuid]), ['reason' => 'Plans changed'])
            ->assertRedirect()->assertSessionHas('status');
        $this->assertSame('cancelled_by_client', $appointment->refresh()->status);
        $this->assertDatabaseHas('appointment_status_histories', ['appointment_id' => $appointment->id, 'to_status' => 'cancelled_by_client', 'reason' => 'Plans changed']);

        $tooLate = $this->appointment($customer, $master, $service, 'Too late');
        $tooLate->update(['appointment_start' => now()->addHours(50), 'appointment_end' => now()->addHours(51)]);
        SiteSettings::where('key', 'cancellation_notice_hours')->update(['payload' => json_encode(72)]);
        $this->delete(route('customer.appointments.destroy', ['appointment' => $tooLate->public_uuid]))
            ->assertRedirect()->assertSessionHasErrors('appointment');
        $this->assertSame('confirmed', $tooLate->refresh()->status);
    }

    public function test_customer_cancellation_can_be_disabled_by_site_setting(): void
    {
        [$master, $service] = $this->bookingEntities();
        $customer = Customer::create(['first_name' => 'No cancel', 'email' => 'no-cancel@example.com', 'phone' => '+37250000019', 'password' => 'secret-pass']);
        $appointment = $this->appointment($customer, $master, $service, 'Cancellation disabled');
        $appointment->update(['appointment_start' => now()->addDays(5), 'appointment_end' => now()->addDays(5)->addHour()]);
        SiteSettings::create(['group' => 'booking', 'key' => 'allow_customer_cancellation', 'payload' => json_encode(false)]);

        $this->actingAs($customer, 'customer')->get(route('customer.dashboard'))
            ->assertOk()
            ->assertSee(__('customer.cancellation_disabled'))
            ->assertDontSee('cancel-reason-'.$appointment->id, false);

        $this->delete(route('customer.appointments.destroy', ['appointment' => $appointment->public_uuid]))
            ->assertRedirect()
            ->assertSessionHasErrors('appointment');

        $this->assertSame('confirmed', $appointment->refresh()->status);
    }

    public function test_pending_reschedule_blocks_old_and_new_slots_until_master_decides(): void
    {
        Carbon::setTestNow('2030-01-01 08:00:00');
        try {
            [$master, $service] = $this->bookingEntities();
            $service->users()->attach($master);
            UserSchedule::create(['user_id'=>$master->id,'tuesday_start'=>'09:00','tuesday_end'=>'18:00']);
            $customer = Customer::create(['first_name'=>'Move','email'=>'move@example.com','phone'=>'+37250000014','password'=>'secret-pass']);
            $appointment = Appointments::create(['customer_id'=>$customer->id,'user_id'=>$master->id,'service_id'=>$service->id,'client_name'=>'Move','client_lastname'=>'Client','client_email'=>$customer->email,'client_phone'=>$customer->phone,'price'=>50,'appointment_start'=>'2030-01-15 10:00','appointment_end'=>'2030-01-15 11:00']);
            $reschedules = app(AppointmentRescheduleService::class);
            $availability = app(SlotAvailabilityService::class);

            $this->actingAs($customer, 'customer')->get(route('customer.appointments.reschedule', ['appointment'=>$appointment->public_uuid]))
                ->assertOk()->assertSee(__('customer.recommended_times'))->assertSee('reschedule-calendar', false);
            $this->getJson(route('customer.appointments.reschedule.days', ['appointment'=>$appointment->public_uuid, 'start'=>'2030-01-15', 'end'=>'2030-01-22']))
                ->assertOk()->assertJsonStructure(['days','limit_date'])->assertJsonPath('days.2030-01-22', 17);

            $request = $reschedules->request($appointment,$customer,'2030-01-22','11:00','12:00','Need another day');
            $this->assertSame('reschedule_requested', $appointment->auditTrail()->latest('id')->value('event'));
            $this->assertFalse($availability->containsSlot('2030-01-15',$service->id,$master->id,'10:00','11:00'));
            $this->assertFalse($availability->containsSlot('2030-01-22',$service->id,$master->id,'11:00','12:00'));

            $reschedules->review($request,$master,true);
            $appointment->refresh();
            $this->assertSame('2030-01-22 11:00:00',$appointment->appointment_start->format('Y-m-d H:i:s'));
            $this->assertTrue($availability->containsSlot('2030-01-15',$service->id,$master->id,'10:00','11:00'));
            $this->assertFalse($availability->containsSlot('2030-01-22',$service->id,$master->id,'11:00','12:00'));
            $this->assertSame('reschedule_approved', $appointment->auditTrail()->latest('id')->value('event'));

            $second = Appointments::create(['customer_id'=>$customer->id,'user_id'=>$master->id,'service_id'=>$service->id,'client_name'=>'Move','client_lastname'=>'Client','client_email'=>$customer->email,'client_phone'=>$customer->phone,'price'=>50,'appointment_start'=>'2030-01-15 12:00','appointment_end'=>'2030-01-15 13:00']);
            $rejected = $reschedules->request($second,$customer,'2030-01-22','13:00','14:00',null);
            $reschedules->review($rejected,$master,false);
            $this->assertSame('reschedule_rejected', $second->auditTrail()->latest('id')->value('event'));
            $this->assertSame('2030-01-15 12:00:00',$second->refresh()->appointment_start->format('Y-m-d H:i:s'));
            $this->assertTrue($availability->containsSlot('2030-01-22',$service->id,$master->id,'13:00','14:00'));
        } finally {
            Carbon::setTestNow();
        }
    }

    private function bookingEntities(): array
    {
        $role = Roles::create(['name' => 'Master']);
        $master = User::factory()->create(['username' => '@customer_master', 'role_id' => $role->id]);
        $service = Services::create(['name' => 'Customer service', 'price' => 50, 'duration_minutes' => 60, 'eventColor' => '#123456', 'status' => true]);
        return [$master, $service];
    }

    private function appointment(Customer $customer, User $master, Services $service, string $name): Appointments
    {
        $start = now()->addDay()->addHours($customer->id);
        return Appointments::create([
            'customer_id' => $customer->id, 'user_id' => $master->id, 'service_id' => $service->id,
            'client_name' => $name, 'client_lastname' => 'Test', 'client_email' => $customer->email,
            'client_phone' => $customer->phone, 'price' => 50,
            'appointment_start' => $start, 'appointment_end' => $start->copy()->addHour(),
        ]);
    }
}
