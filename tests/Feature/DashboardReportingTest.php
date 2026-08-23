<?php

namespace Tests\Feature;

use App\Models\Appointments;
use App\Models\Customer;
use App\Models\Roles;
use App\Models\Services;
use App\Models\User;
use App\Models\WorkShift;
use App\Services\Dashboard\DashboardAnalyticsService;
use App\Services\Dashboard\DashboardPeriod;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DashboardReportingTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_full_dashboard_uses_final_statuses_for_financial_and_customer_metrics(): void
    {
        Carbon::setTestNow('2026-08-23 12:00:00');
        [$admin, $master, $service] = $this->staffAndService();

        $new = $this->customer('new', 1);
        $returning = $this->customer('returning', 2);
        $regular = $this->customer('regular', 3);
        $lost = $this->customer('lost', 4);
        $noShow = $this->customer('noshow', 5);
        $cancelled = $this->customer('cancelled', 6);

        $this->appointment($new, $master, $service, '2026-08-05 09:00:00', 'completed', 100);
        $this->appointment($returning, $master, $service, '2026-07-10 09:00:00', 'completed', 40);
        $this->appointment($returning, $master, $service, '2026-08-06 09:00:00', 'completed', 50);
        $this->appointment($regular, $master, $service, '2026-06-10 09:00:00', 'completed', 40);
        $this->appointment($regular, $master, $service, '2026-07-11 09:00:00', 'completed', 50);
        $this->appointment($regular, $master, $service, '2026-08-07 09:00:00', 'completed', 70);
        $this->appointment($lost, $master, $service, '2026-01-10 09:00:00', 'completed', 40);
        $this->appointment($noShow, $master, $service, '2026-08-08 09:00:00', 'no_show', 80);
        $this->appointment($cancelled, $master, $service, '2026-08-09 09:00:00', 'cancelled_by_client', 60);
        WorkShift::create(['user_id' => $master->id, 'started_at' => '2026-08-10 09:00:00', 'ended_at' => '2026-08-10 17:00:00', 'status' => 'stopped']);

        $report = app(DashboardAnalyticsService::class)->report(DashboardPeriod::from(['period' => 'current_month']));

        $this->assertSame(3, $report['summary']['completed']);
        $this->assertSame(220.0, $report['summary']['revenue']);
        $this->assertSame(80.0, $report['summary']['lost_revenue']);
        $this->assertSame(60.0, $report['summary']['cancelled_value']);
        $this->assertSame(1, $report['customers']['new']);
        $this->assertSame(2, $report['customers']['returning']);
        $this->assertSame(1, $report['customers']['regular']);
        $this->assertSame(1, $report['customers']['lost']);
        $this->assertSame(28800, $report['work_time']['worked_seconds']);

        $this->actingAs($admin)->get(route('admin.dashboard.full'))
            ->assertOk()
            ->assertSee(__('admin_dashboard.full_title'))
            ->assertSee('220,00 €');
    }

    public function test_full_dashboard_requires_dedicated_permission_and_all_customer_scope(): void
    {
        [$admin, $master] = $this->staffAndService();

        $this->actingAs($master)->get(route('admin.dashboard.full'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.dashboard.full', [
            'period' => 'custom', 'from' => '2024-01-01', 'to' => '2026-01-02',
        ]))->assertSessionHasErrors('to');
    }

    public function test_lost_customers_and_unresolved_appointments_are_scoped_to_employee(): void
    {
        Carbon::setTestNow('2026-08-23 12:00:00');
        [$admin, $master, $service] = $this->staffAndService();
        $otherMaster = User::factory()->create(['role_id' => $master->role_id, 'name' => 'Other Master']);
        $ownCustomer = $this->customer('own-lost', 21);
        $otherCustomer = $this->customer('other-lost', 22);

        $this->appointment($ownCustomer, $master, $service, '2026-01-05 09:00:00', 'completed', 50);
        $this->appointment($otherCustomer, $otherMaster, $service, '2026-01-06 09:00:00', 'completed', 60);
        $ownUnresolved = $this->appointment($ownCustomer, $master, $service, '2026-08-20 09:00:00', 'confirmed', 50);
        $otherUnresolved = $this->appointment($otherCustomer, $otherMaster, $service, '2026-08-20 11:00:00', 'confirmed', 60);

        $this->actingAs($master)->get(route('dashboard.customers.lost', ['scope' => 'all']))
            ->assertOk()->assertSee($ownCustomer->email)->assertDontSee($otherCustomer->email);
        $this->actingAs($master)->get(route('dashboard.appointments.unresolved', ['scope' => 'all']))
            ->assertOk()->assertSee(route('calendar.show', $ownUnresolved), false)->assertDontSee(route('calendar.show', $otherUnresolved), false);

        $this->actingAs($admin)->get(route('dashboard.customers.lost', ['scope' => 'all']))
            ->assertOk()->assertSee($ownCustomer->email)->assertSee($otherCustomer->email);
        $this->actingAs($admin)->get(route('dashboard.appointments.unresolved', ['scope' => 'all']))
            ->assertOk()->assertSee('Other Master')->assertSee(route('calendar.show', $otherUnresolved), false);
    }

    public function test_dashboard_charts_separate_final_and_unresolved_statuses(): void
    {
        Carbon::setTestNow('2026-08-23 12:00:00');
        [$admin, $master, $service] = $this->staffAndService();
        $customer = $this->customer('chart', 31);

        $this->appointment($customer, $master, $service, '2026-08-22 09:00:00', 'completed', 100);
        $this->appointment($customer, $master, $service, '2026-08-21 09:00:00', 'confirmed', 80);
        $this->appointment($customer, $master, $service, '2026-08-20 09:00:00', null, 70);
        $this->appointment($customer, $master, $service, '2026-08-19 09:00:00', 'no_show', 60);
        $this->appointment($customer, $master, $service, '2026-08-18 09:00:00', 'cancelled_by_client', 50);
        $this->appointment($customer, $master, $service, '2026-08-23 15:00:00', 'confirmed', 90);

        $week = collect(app(DashboardAnalyticsService::class)->personalCharts($master->id)['week'])->keyBy('date');

        $this->assertSame(1, $week['22.08']['completed']);
        $this->assertSame(100.0, $week['22.08']['revenue']);
        $this->assertSame(1, $week['21.08']['unresolved']);
        $this->assertSame(1, $week['20.08']['unresolved']);
        $this->assertSame(1, $week['19.08']['no_show']);
        $this->assertSame(1, $week['18.08']['cancelled']);
        $this->assertSame(0, $week['23.08']['unresolved']);

        $this->actingAs($admin)->get(route('dashboard'))
            ->assertOk()
            ->assertSee(__('admin_dashboard.chart_completed'))
            ->assertSee(__('admin_dashboard.chart_unresolved'))
            ->assertSee('dashboard-personal-week-chart');

        $this->actingAs($admin)->get(route('admin.dashboard.full'))
            ->assertOk()
            ->assertSee(__('admin_dashboard.chart_no_show'))
            ->assertSee(__('admin_dashboard.chart_cancelled'));
    }

    private function staffAndService(): array
    {
        $adminRole = Roles::create(['name' => 'Administrator', 'slug' => 'super-admin', 'appointment_scope' => 'all', 'is_service_provider' => false]);
        $masterRole = Roles::create(['name' => 'Master', 'slug' => 'master', 'appointment_scope' => 'own', 'is_service_provider' => true]);
        $admin = User::factory()->create(['role_id' => $adminRole->id]);
        $master = User::factory()->create(['role_id' => $masterRole->id]);
        $service = Services::create(['name' => 'Report service', 'price' => 100, 'duration_minutes' => 60, 'eventColor' => '#123456', 'status' => true]);

        return [$admin, $master, $service];
    }

    private function customer(string $name, int $number): Customer
    {
        return Customer::create([
            'first_name' => ucfirst($name),
            'email' => $name.'@example.test',
            'phone' => '+37250000'.str_pad((string) $number, 3, '0', STR_PAD_LEFT),
        ]);
    }

    private function appointment(Customer $customer, User $master, Services $service, string $start, ?string $status, float $price): Appointments
    {
        return Appointments::create([
            'customer_id' => $customer->id,
            'user_id' => $master->id,
            'service_id' => $service->id,
            'client_name' => $customer->first_name,
            'client_lastname' => $customer->last_name ?? '',
            'client_email' => $customer->email,
            'client_phone' => $customer->phone,
            'appointment_start' => $start,
            'appointment_end' => Carbon::parse($start)->addHour(),
            'status' => $status,
            'price' => $price,
        ]);
    }
}
