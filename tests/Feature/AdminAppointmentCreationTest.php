<?php

namespace Tests\Feature;

use App\Models\Appointments;
use App\Models\Customer;
use App\Models\Roles;
use App\Models\Services;
use App\Models\User;
use App\Models\UserSchedule;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminAppointmentCreationTest extends TestCase
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

    public function test_create_page_uses_protected_web_routes_and_requested_date(): void
    {
        [$admin, $master, , $service] = $this->records();

        $this->actingAs($admin)->get(route('calendar.create', ['date' => '2030-01-15']))
            ->assertOk()
            ->assertSee('2030-01-15')
            ->assertSee('calendar\\/availability', false)
            ->assertSee('calendar\\/customers\\/search', false)
            ->assertDontSee('api.booking.getFullyBooked');
    }

    public function test_availability_can_return_nearest_slots_for_any_master(): void
    {
        [$admin, $firstMaster, $secondMaster, $service] = $this->records();
        Appointments::create($this->appointmentPayload($firstMaster, $service, '2030-01-15 09:00:00', '2030-01-15 10:00:00'));

        $response = $this->actingAs($admin)->postJson(route('calendar.availability'), [
            'service_id' => $service->id, 'user_id' => 'all', 'date' => '2030-01-15',
        ])->assertOk()->assertJsonPath('date', '2030-01-15');

        $slots = collect($response->json('slots'));
        $this->assertFalse($slots->contains(fn ($slot) => $slot['user_id'] === $firstMaster->id && $slot['start'] === '09:00'));
        $this->assertTrue($slots->contains(fn ($slot) => $slot['user_id'] === $secondMaster->id && $slot['start'] === '09:00'));
        $this->assertNotEmpty($response->json('recommendations'));
    }

    public function test_admin_creation_links_selected_customer_and_uses_server_price(): void
    {
        [$admin, $master, , $service] = $this->records();
        $customer = Customer::create(['first_name'=>'Existing','last_name'=>'Customer','email'=>'existing@example.test','phone'=>'+37255550001','locale'=>'et']);

        $response = $this->actingAs($admin)->postJson(route('calendar.store'), [
            'customer_id' => $customer->id, 'client_name' => 'Forged', 'client_phone' => '+37255559999',
            'service_id' => $service->id, 'user_id' => $master->id, 'price' => 1,
            'appointment_start' => '2030-01-15 10:00', 'appointment_end' => '2030-01-15 11:00',
        ])->assertOk()->assertJsonStructure(['redirect_url']);

        $appointment = Appointments::firstOrFail();
        $this->assertSame($customer->id, $appointment->customer_id);
        $this->assertSame('Existing', $appointment->client_name);
        $this->assertSame($customer->phone, $appointment->client_phone);
        $this->assertEquals(65, $appointment->price);
        $this->assertFalse($appointment->customer_identity_verified);
    }

    public function test_master_search_and_customer_selection_are_limited_to_own_customer_base(): void
    {
        [$admin, $firstMaster, $secondMaster, $service] = $this->records();
        $own = Customer::create(['first_name'=>'Own','last_name'=>'Client','email'=>'own@example.test','phone'=>'+37255550002','locale'=>'et']);
        $foreign = Customer::create(['first_name'=>'Foreign','last_name'=>'Client','email'=>'foreign@example.test','phone'=>'+37255550003','locale'=>'et']);
        Appointments::create($this->appointmentPayload($firstMaster, $service, '2030-01-15 09:00:00', '2030-01-15 10:00:00') + ['customer_id'=>$own->id,'client_name'=>'Own']);
        Appointments::create($this->appointmentPayload($secondMaster, $service, '2030-01-15 11:00:00', '2030-01-15 12:00:00') + ['customer_id'=>$foreign->id,'client_name'=>'Foreign']);

        $this->actingAs($firstMaster)->getJson(route('calendar.customers.search',['query'=>'Client']))
            ->assertOk()->assertJsonFragment(['name'=>'Own Client'])->assertJsonMissing(['name'=>'Foreign Client']);

        $this->actingAs($firstMaster)->postJson(route('calendar.store'), [
            'customer_id'=>$foreign->id,'client_name'=>'Foreign','client_phone'=>$foreign->phone,
            'service_id'=>$service->id,'user_id'=>$firstMaster->id,
            'appointment_start'=>'2030-01-15 14:00','appointment_end'=>'2030-01-15 15:00',
        ])->assertUnprocessable()->assertJsonValidationErrors('customer_id');
    }

    private function records(): array
    {
        $adminRole = Roles::create(['name'=>'Super Admin']);
        $masterRole = Roles::create(['name'=>'Master']);
        $admin = User::factory()->create(['username'=>'@quick_admin','role_id'=>$adminRole->id,'email_verified_at'=>now()]);
        $first = User::factory()->create(['username'=>'@quick_first','role_id'=>$masterRole->id,'email_verified_at'=>now()]);
        $second = User::factory()->create(['username'=>'@quick_second','role_id'=>$masterRole->id,'email_verified_at'=>now()]);
        foreach ([$first,$second] as $master) {
            UserSchedule::create(['user_id'=>$master->id,'tuesday_start'=>'09:00','tuesday_end'=>'18:00','lunch_start'=>'12:00','lunch_end'=>'13:00']);
        }
        $service = Services::create(['name'=>'Quick service','eventColor'=>'#123456','price'=>65,'duration_minutes'=>60,'status'=>true]);
        $service->users()->attach([$first->id,$second->id]);

        return [$admin,$first,$second,$service];
    }

    private function appointmentPayload(User $master, Services $service, string $start, string $end): array
    {
        return ['status'=>'confirmed','user_id'=>$master->id,'service_id'=>$service->id,'client_name'=>'Booked','client_lastname'=>'Client','client_phone'=>'+37255550009','price'=>65,'appointment_start'=>$start,'appointment_end'=>$end];
    }
}
