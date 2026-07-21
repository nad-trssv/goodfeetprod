<?php

namespace Tests\Feature;

use App\Models\SiteSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BookingTemplateTest extends TestCase
{
    use RefreshDatabase;

    public function test_booking_page_uses_the_template_selected_in_settings(): void
    {
        foreach (['classic' => 'service-first', 'wizard' => 'master-first', 'compact' => null] as $template => $flow) {
            SiteSettings::updateOrCreate(
                ['key' => 'booking_template'],
                ['group' => 'booking', 'payload' => json_encode($template)]
            );

            $response = $this->get(route('serviceBooking'))
                ->assertOk()
                ->assertSee('booking-template--'.$template, false)
                ->assertSee('data-booking-template="'.$template.'"', false);

            if ($flow) {
                $response
                    ->assertSee('data-booking-flow="'.$flow.'"', false)
                    ->assertSee('click.bookingDate', false)
                    ->assertSee('td.fc-day-top[data-date]', false)
                    ->assertSee('scheduleAvailabilityRange(view.start, view.end)', false)
                    ->assertSee('range_start: rangeStart', false)
                    ->assertSee('range_end: rangeEnd', false);
            }
        }
    }

    public function test_invalid_stored_template_falls_back_to_classic(): void
    {
        SiteSettings::create([
            'key' => 'booking_template',
            'group' => 'booking',
            'payload' => json_encode('unknown-template'),
        ]);

        $this->get(route('serviceBooking'))
            ->assertOk()
            ->assertSee('booking-template--classic', false);
    }
}
