<?php

namespace Tests\Feature;

use App\Http\Requests\BookingRequest;
use App\Models\Appointments;
use App\Models\Customer;
use App\Models\Roles;
use App\Models\Services;
use App\Models\User;
use App\Services\Booking\BookingCreator;
use App\Services\Booking\RoomAllocationService;
use App\Services\Booking\SlotAvailabilityService;
use App\Models\UserSchedule;
use Carbon\Carbon;
use App\Services\Customer\CustomerIdentityService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class BookingCreatorTest extends TestCase
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

    public function test_it_uses_server_price_and_generates_public_uuid(): void
    {
        [$user, $service] = $this->bookingEntities('75.50');
        $creator = new BookingCreator($this->roomServiceWithoutAssignment(), app(SlotAvailabilityService::class), app(CustomerIdentityService::class));

        $appointment = $creator->create(
            $this->request($user, $service),
            ['start' => '10:00', 'end' => '11:00'],
            $service,
        );

        $this->assertEquals(75.50, $appointment->price);
        $this->assertNotNull($appointment->public_uuid);
        $this->assertMatchesRegularExpression('/^[0-9a-f-]{36}$/', $appointment->public_uuid);
        $this->assertDatabaseHas('appointment_audits', [
            'appointment_id' => $appointment->id,
            'event' => 'created',
            'actor_id' => $appointment->customer_id,
        ]);
    }

    public function test_it_rejects_an_overlapping_booking_for_the_same_master(): void
    {
        [$user, $service] = $this->bookingEntities('50.00');
        $creator = new BookingCreator($this->roomServiceWithoutAssignment(), app(SlotAvailabilityService::class), app(CustomerIdentityService::class));
        $request = $this->request($user, $service);

        Appointments::create([
            'user_id' => $user->id,
            'service_id' => $service->id,
            'price' => 50,
            'appointment_start' => '2030-01-15 10:30:00',
            'appointment_end' => '2030-01-15 11:30:00',
            'client_name' => 'Existing',
            'client_lastname' => 'Client',
        ]);

        try {
            $creator->create($request, ['start' => '10:00', 'end' => '11:00'], $service);
            $this->fail('An overlapping booking was accepted.');
        } catch (HttpException $exception) {
            $this->assertSame(409, $exception->getStatusCode());
        }

        $this->assertSame(1, Appointments::count());
    }

    public function test_authenticated_customer_cannot_book_with_another_identity(): void
    {
        [$user, $service] = $this->bookingEntities('50.00');
        $customer = Customer::create([
            'first_name' => 'Account',
            'email' => 'account@example.com',
            'phone' => '+37250000010',
            'password' => 'secret-pass',
        ]);
        $this->actingAs($customer, 'customer');

        $creator = new BookingCreator($this->roomServiceWithoutAssignment(), app(SlotAvailabilityService::class), app(CustomerIdentityService::class));

        $this->expectException(ValidationException::class);
        $creator->create($this->request($user, $service), ['start' => '10:00', 'end' => '11:00'], $service);
    }

    private function bookingEntities(string $price): array
    {
        $role = Roles::create(['name' => 'master']);
        $user = User::factory()->create(['username' => '@booking_'.fake()->unique()->numerify('#####'), 'role_id' => $role->id]);
        $service = Services::create([
            'name' => 'Test service',
            'eventColor' => '#000000',
            'price' => $price,
            'duration_minutes' => 60,
            'status' => true,
        ]);
        $service->users()->attach($user);
        UserSchedule::create([
            'user_id' => $user->id,
            'tuesday_start' => '09:00',
            'tuesday_end' => '18:00',
        ]);

        return [$user, $service];
    }

    private function request(User $user, Services $service): BookingRequest
    {
        return BookingRequest::create('/booking', 'POST', [
            'user_id' => $user->id,
            'service_id' => $service->id,
            'choose_date' => '2030-01-15',
            'appointment_start' => '10:00',
            'appointment_end' => '11:00',
            'client_name' => 'Test',
            'client_lastname' => 'Client',
            'client_email' => 'client@example.com',
            'client_phone' => '+37255555555',
        ]);
    }

    private function roomServiceWithoutAssignment(): RoomAllocationService
    {
        $rooms = $this->createMock(RoomAllocationService::class);
        $rooms->method('assign')->willReturn(null);

        return $rooms;
    }
}
