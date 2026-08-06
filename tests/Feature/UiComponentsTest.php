<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Blade;
use Tests\TestCase;

class UiComponentsTest extends TestCase
{
    public function test_avatar_uses_local_initials_when_photo_is_missing(): void
    {
        $html = Blade::render('<x-ui.avatar name="Anna Tamm" :size="48" online />');

        $this->assertStringContainsString('AT', $html);
        $this->assertStringContainsString('width:48px', $html);
        $this->assertStringContainsString('Онлайн', $html);
        $this->assertStringNotContainsString('ui-avatars.com', $html);
    }

    public function test_service_image_accepts_array_data_and_keeps_lazy_loading(): void
    {
        $html = Blade::render(
            '<x-ui.service-image :service="$service" :width="56" :height="56" class="rounded" />',
            ['service' => ['name' => 'Маникюр', 'image_url' => '/storage/services/manicure.webp']]
        );

        $this->assertStringContainsString('/storage/services/manicure.webp', $html);
        $this->assertStringContainsString('loading="lazy"', $html);
        $this->assertStringContainsString('width="56"', $html);
    }

    public function test_appointment_status_has_consistent_label_and_tone(): void
    {
        app()->setLocale('ru');
        $html = Blade::render('<x-appointments.status status="cancelled_by_client" />');

        $this->assertStringContainsString('Отменена клиентом', $html);
        $this->assertStringContainsString('badge-phoenix-secondary', $html);
    }
}
