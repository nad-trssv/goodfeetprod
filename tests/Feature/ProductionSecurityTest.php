<?php

namespace Tests\Feature;

use App\Models\Appointments;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProductionSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_internal_api_is_not_registered(): void
    {
        $this->get('/api/booking/store')->assertNotFound();
        $this->get('/api/settings/getRedDays')->assertNotFound();
    }

    public function test_admin_ajax_routes_require_authentication(): void
    {
        $this->get(route('settings.red-days.index'))->assertRedirect();
        $this->post(route('profile.ajax.update'))->assertRedirect();
    }

    public function test_booking_mutation_uses_web_csrf_middleware(): void
    {
        $route = app('router')->getRoutes()->getByName('booking.store');

        $this->assertContains('web', $route->gatherMiddleware());
    }

    public function test_confirmation_uses_uuid_and_numeric_ids_do_not_resolve(): void
    {
        $appointment = new Appointments(['public_uuid' => (string) Str::uuid()]);
        $appointment->id = 123;

        $url = route('booking.show', ['appointment' => $appointment->public_uuid]);
        $this->assertStringContainsString($appointment->public_uuid, $url);
        $this->get('/booking/confirmation/123')->assertNotFound();
    }

    public function test_database_has_unique_master_start_constraint(): void
    {
        $indexes = collect(Schema::getIndexes('appointments'));

        $this->assertTrue($indexes->contains(fn ($index) =>
            $index['unique'] && $index['columns'] === ['user_id', 'appointment_start']
        ));
    }
}
