<?php

namespace Tests\Feature;

use App\Models\MessagingIntegration;
use App\Models\Permission;
use App\Models\Roles;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class MessagingIntegrationSettingsTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_store_messaging_configuration_with_encrypted_secrets(): void
    {
        $admin = $this->admin();
        $token = 'permanent-whatsapp-token-very-secret';

        $this->actingAs($admin)->put(route('settings.messaging.update', 'whatsapp'), [
            'enabled' => '1',
            'settings' => [
                'business_portfolio_id' => '10001',
                'business_account_id' => '20002',
                'phone_number_id' => '30003',
                'app_id' => '40004',
            ],
            'credentials' => [
                'access_token' => $token,
                'app_secret' => 'meta-app-secret',
                'webhook_verify_token' => 'our-webhook-secret',
            ],
        ])->assertRedirect(route('settings.index').'#tab-integrations');

        $integration = MessagingIntegration::where('provider', 'whatsapp')->firstOrFail();
        $rawCredentials = DB::table('messaging_integrations')->where('provider', 'whatsapp')->value('credentials');

        $this->assertTrue($integration->enabled);
        $this->assertTrue($integration->isConfigured());
        $this->assertSame($token, $integration->credentials['access_token']);
        $this->assertStringNotContainsString($token, $rawCredentials);

        $this->actingAs($admin)->get(route('settings.index'))
            ->assertOk()
            ->assertSee(__('admin_messaging.secret_saved'))
            ->assertDontSee($token);
    }

    public function test_blank_secret_keeps_existing_value_and_clear_option_removes_it(): void
    {
        $admin = $this->admin();
        MessagingIntegration::create([
            'provider' => 'telegram',
            'enabled' => true,
            'settings' => ['bot_username' => '@booking_test_bot'],
            'credentials' => ['bot_token' => '123456:original', 'webhook_secret' => 'original_secret'],
        ]);

        $this->actingAs($admin)->put(route('settings.messaging.update', 'telegram'), [
            'enabled' => '1',
            'settings' => ['bot_username' => '@booking_test_bot'],
            'credentials' => ['bot_token' => '', 'webhook_secret' => ''],
        ])->assertRedirect();

        $integration = MessagingIntegration::where('provider', 'telegram')->firstOrFail();
        $this->assertSame('123456:original', $integration->credentials['bot_token']);

        $this->actingAs($admin)->put(route('settings.messaging.update', 'telegram'), [
            'enabled' => '0',
            'clear_credentials' => '1',
            'settings' => ['bot_username' => '@booking_test_bot'],
        ])->assertRedirect();

        $this->assertNull($integration->fresh()->credentials);
        $this->assertFalse($integration->fresh()->enabled);
    }

    public function test_non_admin_cannot_change_messaging_credentials_even_with_settings_permission(): void
    {
        $role = Roles::create([
            'name' => 'Integration manager',
            'slug' => 'integration-manager',
            'appointment_scope' => 'all',
        ]);
        $role->permissions()->attach(Permission::where('key', 'settings.update')->value('id'));
        $employee = User::factory()->create(['role_id' => $role->id, 'email_verified_at' => now()]);

        $this->actingAs($employee)->put(route('settings.messaging.update', 'viber'), [
            'enabled' => '1',
            'settings' => ['bot_name' => 'Booking', 'bot_uri' => 'booking'],
            'credentials' => ['auth_token' => 'must-not-be-saved'],
        ])->assertForbidden();

        $this->assertDatabaseCount('messaging_integrations', 0);
    }

    private function admin(): User
    {
        $role = Roles::create([
            'name' => 'Super Admin',
            'slug' => 'super-admin',
            'appointment_scope' => 'all',
            'is_system' => true,
        ]);

        return User::factory()->create([
            'username' => '@messaging_admin',
            'role_id' => $role->id,
            'email_verified_at' => now(),
            'locale' => 'ru',
        ]);
    }
}
