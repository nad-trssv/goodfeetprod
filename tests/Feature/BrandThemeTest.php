<?php

namespace Tests\Feature;

use App\Models\Roles;
use App\Models\SiteSettings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BrandThemeTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_change_the_admin_accent_without_changing_public_frontend(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->post(route('settings.main.update'), $this->settingsPayload('#A12BC3'))
            ->assertOk();

        $this->assertSame(
            '#A12BC3',
            json_decode(SiteSettings::where('key', 'primary_accent_color')->value('payload'), true)
        );
        $this->assertDatabaseHas('site_settings', [
            'key' => 'primary_accent_color',
            'group' => 'admin_branding',
        ]);

        $this->actingAs($admin)->get(route('settings.index'))
            ->assertOk()
            ->assertSee('--brand-accent: #A12BC3;', false)
            ->assertSee('--brand-accent-contrast: #FFFFFF;', false);

        $this->get(route('serviceBooking'))
            ->assertOk()
            ->assertDontSee('--brand-accent: #A12BC3;', false)
            ->assertDontSee('id="brand-theme"', false);
    }

    public function test_accent_color_must_be_a_six_digit_hex_value(): void
    {
        $admin = $this->superAdmin();

        $this->actingAs($admin)->postJson(route('settings.main.update'), $this->settingsPayload('blue'))
            ->assertUnprocessable()
            ->assertJsonValidationErrors('primary_accent_color');

        $this->assertDatabaseMissing('site_settings', ['key' => 'primary_accent_color']);
    }

    public function test_changed_color_is_used_on_the_next_request_without_stale_cache(): void
    {
        SiteSettings::create([
            'key' => 'primary_accent_color',
            'group' => 'branding',
            'payload' => json_encode('#112233'),
        ]);

        $admin = $this->superAdmin();
        $this->actingAs($admin)->get(route('settings.index'))->assertSee('--brand-accent: #112233;', false);

        SiteSettings::where('key', 'primary_accent_color')->update(['payload' => json_encode('#F2D544')]);

        $this->actingAs($admin)->get(route('settings.index'))
            ->assertSee('--brand-accent: #F2D544;', false)
            ->assertSee('--brand-accent-contrast: #111111;', false);
    }

    private function settingsPayload(string $color): array
    {
        return [
            'company_name' => 'Test company',
            'company_email' => 'company@example.test',
            'primary_accent_color' => $color,
            'booking_template' => 'classic',
            'show_service_images' => '1',
            'show_master_images' => '1',
        ];
    }

    private function superAdmin(): User
    {
        $role = Roles::create(['name' => 'Super Admin']);

        return User::factory()->create([
            'username' => '@theme_admin',
            'role_id' => $role->id,
            'email_verified_at' => now(),
        ]);
    }
}
