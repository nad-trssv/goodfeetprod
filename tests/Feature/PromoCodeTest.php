<?php

namespace Tests\Feature;

use App\Http\Requests\BookingRequest;
use App\Models\Appointments;
use App\Models\Customer;
use App\Models\PromoCode;
use App\Models\Roles;
use App\Models\Services;
use App\Models\User;
use App\Models\UserSchedule;
use App\Services\Booking\BookingCreator;
use App\Services\Booking\PromoCodePricingService;
use App\Services\Booking\RoomAllocationService;
use App\Services\Booking\SlotAvailabilityService;
use App\Services\Customer\CustomerIdentityService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class PromoCodeTest extends TestCase
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

    public function test_percentage_promo_is_calculated_server_side_and_snapshotted(): void
    {
        [$master, $service] = $this->entities(100);
        $promo = PromoCode::create(['code'=>'SAVE20','discount_type'=>'percentage','discount_value'=>20,'active'=>true]);

        $appointment = $this->creator()->create($this->bookingRequest($master, $service, '10:00', 'SAVE20', ['price' => 0.01]), ['start'=>'10:00','end'=>'11:00'], $service);

        $this->assertSame('100.00', $appointment->original_price);
        $this->assertSame('20.00', $appointment->discount_amount);
        $this->assertSame('80.00', $appointment->price);
        $this->assertSame('SAVE20', $appointment->promo_code);
        $this->assertDatabaseHas('promo_code_redemptions', ['promo_code_id'=>$promo->id,'appointment_id'=>$appointment->id,'discount_amount'=>20]);
    }

    public function test_fixed_discount_cannot_make_price_negative(): void
    {
        [$master, $service] = $this->entities(30);
        PromoCode::create(['code'=>'FREEISH','discount_type'=>'fixed','discount_value'=>100,'active'=>true]);

        $appointment = $this->creator()->create($this->bookingRequest($master, $service, '10:00', 'FREEISH'), ['start'=>'10:00','end'=>'11:00'], $service);

        $this->assertSame('30.00', $appointment->discount_amount);
        $this->assertSame('0.00', $appointment->price);
    }

    public function test_customer_and_total_usage_limits_are_enforced(): void
    {
        [$master, $service] = $this->entities(50);
        PromoCode::create(['code'=>'ONCE','discount_type'=>'fixed','discount_value'=>5,'usage_limit'=>5,'per_customer_limit'=>1,'active'=>true]);
        $creator = $this->creator();
        $creator->create($this->bookingRequest($master, $service, '10:00', 'ONCE'), ['start'=>'10:00','end'=>'11:00'], $service);

        $this->expectException(ValidationException::class);
        $creator->create($this->bookingRequest($master, $service, '12:00', 'ONCE'), ['start'=>'12:00','end'=>'13:00'], $service);
    }

    public function test_service_period_and_minimum_restrictions_are_enforced(): void
    {
        [, $service] = $this->entities(50);
        $other = Services::create(['name'=>'Other','price'=>100,'duration_minutes'=>60,'eventColor'=>'#111111','status'=>true]);
        $promo = PromoCode::create(['code'=>'LIMITED','discount_type'=>'percentage','discount_value'=>10,'minimum_amount'=>60,'valid_from'=>'2030-01-01','valid_until'=>'2030-01-31','active'=>true]);
        $promo->services()->attach($other);

        try {
            app(PromoCodePricingService::class)->quote($service, '2030-01-15', 'LIMITED', null, 'client@example.com');
            $this->fail('Restricted promo code was accepted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('promo_code', $exception->errors());
        }

        $promo->services()->sync([$service->id]);
        $this->expectException(ValidationException::class);
        app(PromoCodePricingService::class)->quote($service, '2030-01-15', 'LIMITED', null, 'client@example.com');
    }

    public function test_preview_route_returns_only_server_calculated_prices(): void
    {
        [, $service] = $this->entities(90);
        PromoCode::create(['code'=>'PREVIEW','discount_type'=>'percentage','discount_value'=>25,'active'=>true]);

        $this->postJson(route('booking.promo.preview'), ['promo_code'=>'preview','service_id'=>$service->id,'choose_date'=>'2030-01-15','client_email'=>'new@example.com','price'=>1])
            ->assertOk()->assertJson(['code'=>'PREVIEW','original_price'=>90,'discount_amount'=>22.5,'final_price'=>67.5]);
    }

    public function test_only_super_admin_can_manage_promo_codes(): void
    {
        $role = Roles::create(['name'=>'Admin']);
        $admin = User::factory()->create(['username'=>'@promo_admin','role_id'=>$role->id,'email_verified_at'=>now()]);
        $service = Services::create(['name'=>'Admin promo service','price'=>70,'duration_minutes'=>60,'eventColor'=>'#123456','status'=>true]);

        $this->get(route('promo-codes.index'))->assertRedirect();
        $this->actingAs($admin)->post(route('promo-codes.store'), [
            'code'=>'summer-30','discount_type'=>'percentage','discount_value'=>30,'minimum_amount'=>50,
            'usage_limit'=>100,'per_customer_limit'=>1,'first_booking_only'=>0,'active'=>1,'services'=>[$service->id],
        ])->assertRedirect()->assertSessionHas('success');

        $promo = PromoCode::firstOrFail();
        $this->assertSame('SUMMER-30', $promo->code);
        $this->assertTrue($promo->services()->whereKey($service->id)->exists());
    }

    private function entities(float $price): array
    {
        $role = Roles::create(['name'=>'Master']);
        $master = User::factory()->create(['username'=>'@promo_'.fake()->unique()->numerify('#####'),'role_id'=>$role->id]);
        $service = Services::create(['name'=>'Promo service','price'=>$price,'duration_minutes'=>60,'eventColor'=>'#123456','status'=>true]);
        $service->users()->attach($master);
        UserSchedule::create(['user_id'=>$master->id,'tuesday_start'=>'09:00','tuesday_end'=>'18:00']);
        return [$master, $service];
    }

    private function bookingRequest(User $master, Services $service, string $start, string $code, array $extra = []): BookingRequest
    {
        return BookingRequest::create('/booking', 'POST', array_merge([
            'user_id'=>$master->id,'service_id'=>$service->id,'choose_date'=>'2030-01-15','appointment_start'=>$start,
            'appointment_end'=>Carbon::createFromFormat('H:i',$start)->addHour()->format('H:i'),'client_name'=>'Promo','client_lastname'=>'Client',
            'client_email'=>'promo@example.com','client_phone'=>'+37255555111','promo_code'=>$code,
        ], $extra));
    }

    private function creator(): BookingCreator
    {
        $rooms = $this->createMock(RoomAllocationService::class);
        $rooms->method('assign')->willReturn(null);
        return new BookingCreator($rooms, app(SlotAvailabilityService::class), app(CustomerIdentityService::class), null, app(PromoCodePricingService::class));
    }
}
