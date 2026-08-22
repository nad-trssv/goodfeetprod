<?php

namespace Tests\Feature;

use App\Models\Appointments;
use App\Models\Customer;
use App\Models\Roles;
use App\Models\Services;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminCustomerDirectoryTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_sees_one_customer_with_booking_counts_for_each_master(): void
    {
        [$admin, $firstMaster, $secondMaster, $service] = $this->records();
        $shared = Customer::create(['first_name'=>'Anna','last_name'=>'Shared','email'=>'anna@example.test','phone'=>'+37250000001','locale'=>'ru']);
        $other = Customer::create(['first_name'=>'Berta','last_name'=>'Other','email'=>'berta@example.test','phone'=>'+37250000002','locale'=>'et']);
        $this->appointment($shared, $firstMaster, $service, '2030-01-10 09:00:00', 'old-anna@example.test');
        $this->appointment($shared, $secondMaster, $service, '2030-01-11 09:00:00');
        $this->appointment($other, $secondMaster, $service, '2030-01-12 09:00:00');

        $response = $this->actingAs($admin)->get(route('admin.customers.index'))
            ->assertOk()->assertSee('Anna Shared')->assertSee('Berta Other')
            ->assertSee($firstMaster->name)->assertSee($secondMaster->name);
        $this->assertSame(1, substr_count($response->getContent(), 'data-customer-id="'.$shared->id.'"'));

        $this->actingAs($admin)->get(route('admin.customers.index', ['search'=>'old-anna@example.test']))
            ->assertOk()->assertSee('Anna Shared')->assertDontSee('Berta Other');
    }

    public function test_master_only_sees_customers_who_booked_with_them_even_when_query_requests_another_master(): void
    {
        [, $firstMaster, $secondMaster, $service] = $this->records();
        $shared = Customer::create(['first_name'=>'Shared','last_name'=>'Client','email'=>'shared@example.test','phone'=>'+37250000003','locale'=>'et']);
        $other = Customer::create(['first_name'=>'Other','last_name'=>'Client','email'=>'other@example.test','phone'=>'+37250000004','locale'=>'et']);
        $this->appointment($shared, $firstMaster, $service, '2030-01-10 09:00:00');
        $this->appointment($shared, $secondMaster, $service, '2030-01-11 09:00:00');
        $this->appointment($other, $secondMaster, $service, '2030-01-12 09:00:00');

        $this->actingAs($firstMaster)->get(route('admin.customers.index', ['master_id'=>$secondMaster->id]))
            ->assertOk()->assertSee('Shared Client')->assertDontSee('Other Client')
            ->assertSee($firstMaster->name)->assertDontSee($secondMaster->name);
    }

    public function test_customer_filter_links_to_only_that_customers_appointments(): void
    {
        [$admin, $master, , $service] = $this->records();
        $customer = Customer::create(['first_name'=>'Link','last_name'=>'Client','email'=>'link@example.test','phone'=>'+37250000005','locale'=>'et']);
        $other = Customer::create(['first_name'=>'Hidden','last_name'=>'Client','email'=>'hidden@example.test','phone'=>'+37250000006','locale'=>'et']);
        $visible = $this->appointment($customer, $master, $service, '2030-01-10 09:00:00');
        $hidden = $this->appointment($other, $master, $service, '2030-01-11 09:00:00');

        $this->actingAs($admin)->get(route('calendarList', ['customer_id'=>$customer->id]))
            ->assertOk()->assertSee('Link Client')->assertDontSee('Hidden Client');
    }

    public function test_customer_profile_shows_aggregates_preferences_and_possible_guest_bookings(): void
    {
        [$admin, $master, , $service] = $this->records();
        $customer = Customer::create([
            'first_name'=>'Profile','last_name'=>'Customer','email'=>'profile@example.test','phone'=>'+37250000007',
            'password'=>Hash::make('secret-pass'),'account_registered_at'=>'2026-08-01 12:00:00','locale'=>'ru',
        ]);
        $verified = $this->appointment($customer, $master, $service, '2026-08-05 09:00:00');
        $verified->update(['customer_identity_verified'=>true, 'price'=>80]);
        $possible = $this->appointment($customer, $master, $service, '2026-08-04 09:00:00');
        $possible->update(['customer_identity_verified'=>false, 'price'=>60]);

        $response = $this->actingAs($admin)->withSession(['locale'=>'ru'])
            ->get(route('admin.customers.show', $customer));

        $response->assertOk()
            ->assertSee('Profile Customer')
            ->assertSee('Аккаунт создан')
            ->assertSee('Возможные записи')
            ->assertSee('140,00 €')
            ->assertSee($master->name)
            ->assertSee($service->name);
        $this->assertSame(1, substr_count($response->getContent(), 'Из аккаунта'));
        $this->assertSame(1, substr_count($response->getContent(), 'По контактам'));
        $response->assertSee('Email и телефон');
    }

    public function test_master_cannot_open_customer_who_has_no_booking_with_them(): void
    {
        [, $firstMaster, $secondMaster, $service] = $this->records();
        $customer = Customer::create(['first_name'=>'Private','last_name'=>'Customer','email'=>'private@example.test','phone'=>'+37250000008','locale'=>'et']);
        $this->appointment($customer, $secondMaster, $service, '2030-01-12 09:00:00');

        $this->actingAs($firstMaster)->get(route('admin.customers.show', $customer))->assertForbidden();
        $this->actingAs($secondMaster)->get(route('admin.customers.show', $customer))->assertOk();
    }

    public function test_directory_uses_guest_and_customer_labels_and_round_specialist_avatars(): void
    {
        [$admin, $master, , $service] = $this->records();
        $guest = Customer::create(['first_name'=>'Guest','last_name'=>'Visitor','email'=>'guest-label@example.test','phone'=>'+37250000009','locale'=>'et']);
        $account = Customer::create(['first_name'=>'Account','last_name'=>'Owner','email'=>'account-label@example.test','phone'=>'+37250000010','password'=>'secret-pass','locale'=>'et']);
        $this->appointment($guest, $master, $service, '2030-01-10 09:00:00');
        $this->appointment($account, $master, $service, '2030-01-11 09:00:00');
        $admin->update(['locale' => 'et']);

        $this->actingAs($admin)->get(route('admin.customers.index'))
            ->assertOk()->assertSee('Külaline')->assertSee('Klient')->assertSee('avatar-group avatar-group-dense', false);
    }

    private function records(): array
    {
        $adminRole = Roles::create(['name'=>'Super Admin']);
        $masterRole = Roles::create(['name'=>'Master']);
        $admin = User::factory()->create(['username'=>'@customer_admin','role_id'=>$adminRole->id]);
        $first = User::factory()->create(['name'=>'Master One','username'=>'@master_one','role_id'=>$masterRole->id]);
        $second = User::factory()->create(['name'=>'Master Two','username'=>'@master_two','role_id'=>$masterRole->id]);
        $service = Services::create(['name'=>'Service','eventColor'=>'#123456','price'=>50,'duration_minutes'=>60,'status'=>true]);
        return [$admin, $first, $second, $service];
    }

    private function appointment(Customer $customer, User $master, Services $service, string $start, ?string $snapshotEmail = null): Appointments
    {
        return Appointments::create([
            'customer_id'=>$customer->id,'status'=>'completed','user_id'=>$master->id,'service_id'=>$service->id,
            'client_name'=>$customer->first_name,'client_lastname'=>$customer->last_name,
            'client_email'=>$snapshotEmail ?: $customer->email,'client_phone'=>$customer->phone,'price'=>50,
            'appointment_start'=>$start,'appointment_end'=>date('Y-m-d H:i:s', strtotime($start.' +1 hour')),
        ]);
    }
}
