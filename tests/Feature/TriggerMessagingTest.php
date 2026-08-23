<?php

namespace Tests\Feature;

use App\Models\Appointments;
use App\Models\Customer;
use App\Models\MessagingIntegration;
use App\Models\MessagingTriggerSetting;
use App\Models\Roles;
use App\Models\Services;
use App\Models\TriggerMessageDispatch;
use App\Models\User;
use App\Services\Localization\SiteLocaleRegistry;
use App\Services\Messaging\TriggerMessageDispatcher;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class TriggerMessagingTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    public function test_appointment_snapshots_language_and_schedules_each_trigger(): void
    {
        Carbon::setTestNow('2030-01-01 09:00:00');
        [$master, $service, $customer] = $this->records();

        $appointment = $this->appointment($master, $service, $customer, '2030-01-03 10:00:00', 'confirmed', 'ru');

        $this->assertSame('ru', $appointment->fresh()->client_locale);
        $this->assertDatabaseHas('trigger_message_dispatches', [
            'appointment_id' => $appointment->id, 'trigger' => 'booking_created', 'status' => 'pending',
        ]);
        $this->assertDatabaseHas('trigger_message_dispatches', [
            'appointment_id' => $appointment->id, 'trigger' => 'appointment_reminder',
            'scheduled_at' => '2030-01-02 10:00:00',
        ]);
        $this->assertDatabaseHas('trigger_message_dispatches', [
            'appointment_id' => $appointment->id, 'trigger' => 'review_request',
            'scheduled_at' => '2030-01-03 13:00:00',
        ]);
        $this->assertDatabaseHas('appointment_feedback', ['appointment_id' => $appointment->id]);
    }

    public function test_dispatcher_falls_back_only_after_definitive_provider_failure(): void
    {
        Carbon::setTestNow('2030-01-01 09:00:00');
        [$master, $service, $customer] = $this->records();
        $customer->update(['messaging_contacts' => ['viber_user_id' => 'viber-customer-1']]);
        MessagingTriggerSetting::where('trigger', 'booking_created')->update(['enabled' => true]);
        MessagingIntegration::create([
            'provider' => 'whatsapp', 'enabled' => true, 'priority' => 1,
            'settings' => [
                'business_account_id'=>'100', 'phone_number_id'=>'200',
                'booking_created_template'=>'booking_created',
                'template_language_codes'=>'{"ru":"ru"}',
            ],
            'credentials' => ['access_token' => 'meta-token'],
        ]);
        MessagingIntegration::create([
            'provider' => 'viber', 'enabled' => true, 'priority' => 2,
            'settings' => ['bot_name'=>'Booking', 'bot_uri'=>'booking'],
            'credentials' => ['auth_token'=>'viber-token'],
        ]);
        MessagingIntegration::create([
            'provider' => 'telegram', 'enabled' => true, 'priority' => 3,
            'settings' => ['bot_username'=>'booking_test_bot'],
            'credentials' => ['bot_token'=>'telegram-token', 'webhook_secret'=>'webhook_secret'],
        ]);
        Http::fake([
            'graph.facebook.com/*' => Http::response(['error'=>['message'=>'recipient rejected']], 400),
            'chatapi.viber.com/*' => Http::response(['status'=>0, 'message_token'=>'viber-message-7'], 200),
            'api.telegram.org/*' => Http::response(['ok'=>true, 'result'=>['message_id'=>9]], 200),
        ]);

        $appointment = $this->appointment($master, $service, $customer, '2030-01-03 10:00:00', 'confirmed', 'ru');
        $this->assertSame(1, app(TriggerMessageDispatcher::class)->processDue());

        $dispatch = TriggerMessageDispatch::where('appointment_id', $appointment->id)->where('trigger', 'booking_created')->firstOrFail();
        $this->assertSame('sent', $dispatch->status);
        $this->assertSame('viber', $dispatch->sent_provider);
        $this->assertSame(['whatsapp', 'viber'], $dispatch->attempts()->orderBy('id')->pluck('provider')->all());
        Http::assertSentCount(2);
    }

    public function test_viber_business_error_with_http_200_falls_back_to_telegram(): void
    {
        Carbon::setTestNow('2030-01-01 09:00:00');
        [$master, $service, $customer] = $this->records();
        $customer->update(['messaging_contacts' => [
            'viber_user_id' => 'unavailable-viber-user',
            'telegram_chat_id' => 'telegram-customer-1',
        ]]);
        MessagingTriggerSetting::where('trigger', 'booking_created')->update(['enabled' => true]);
        MessagingIntegration::create([
            'provider' => 'viber', 'enabled' => true, 'priority' => 1,
            'settings' => ['bot_name' => 'Booking', 'bot_uri' => 'booking'],
            'credentials' => ['auth_token' => 'viber-token'],
        ]);
        MessagingIntegration::create([
            'provider' => 'telegram', 'enabled' => true, 'priority' => 2,
            'settings' => ['bot_username' => 'booking_test_bot'],
            'credentials' => ['bot_token' => 'telegram-token', 'webhook_secret' => 'webhook-secret'],
        ]);
        Http::fake([
            'chatapi.viber.com/*' => Http::response(['status' => 6, 'status_message' => 'Not subscribed'], 200),
            'api.telegram.org/*' => Http::response(['ok' => true, 'result' => ['message_id' => 10]], 200),
        ]);

        $appointment = $this->appointment($master, $service, $customer, '2030-01-03 10:00:00', 'confirmed', 'ru');
        $this->assertSame(1, app(TriggerMessageDispatcher::class)->processDue());

        $dispatch = TriggerMessageDispatch::where('appointment_id', $appointment->id)
            ->where('trigger', 'booking_created')->firstOrFail();
        $this->assertSame('telegram', $dispatch->sent_provider);
        $this->assertSame(['failed', 'sent'], $dispatch->attempts()->orderBy('id')->pluck('status')->all());
        Http::assertSentCount(2);
    }

    public function test_public_feedback_uses_token_requires_completed_visit_and_is_idempotent(): void
    {
        Carbon::setTestNow('2030-01-03 12:00:00');
        [$master, $service, $customer] = $this->records();
        $appointment = $this->appointment($master, $service, $customer, '2030-01-03 10:00:00', 'confirmed', 'et');
        $feedback = $appointment->feedback()->firstOrFail();

        $this->get(route('appointment-feedback.show', $feedback))->assertNotFound();
        $appointment->update(['status' => 'completed']);

        $this->get(route('appointment-feedback.show', $feedback))
            ->assertOk()->assertSee('Hinda broneeringut')->assertDontSee($appointment->client_phone);
        $this->post(route('appointment-feedback.store', $feedback), ['rating'=>5])->assertRedirect();
        $this->post(route('appointment-feedback.store', $feedback), ['rating'=>1])->assertRedirect();

        $this->assertDatabaseHas('appointment_feedback', [
            'id'=>$feedback->id, 'rating'=>5,
        ]);
    }

    public function test_signed_delivery_failure_webhook_advances_to_the_next_channel(): void
    {
        Carbon::setTestNow('2030-01-01 09:00:00');
        [$master, $service, $customer] = $this->records();
        $customer->update(['messaging_contacts'=>['viber_user_id'=>'viber-customer-2']]);
        MessagingTriggerSetting::where('trigger', 'booking_created')->update(['enabled'=>true]);
        MessagingIntegration::create([
            'provider'=>'whatsapp', 'enabled'=>true, 'priority'=>1,
            'settings'=>['business_account_id'=>'100','phone_number_id'=>'200','booking_created_template'=>'booking_created'],
            'credentials'=>['access_token'=>'meta-token','app_secret'=>'meta-secret','webhook_verify_token'=>'verify-me'],
        ]);
        MessagingIntegration::create([
            'provider'=>'viber', 'enabled'=>true, 'priority'=>2,
            'settings'=>['bot_name'=>'Booking','bot_uri'=>'booking'],
            'credentials'=>['auth_token'=>'viber-token'],
        ]);
        Http::fake([
            'graph.facebook.com/*'=>Http::response(['messages'=>[['id'=>'wamid.123']]], 200),
            'chatapi.viber.com/*'=>Http::response(['status'=>0,'message_token'=>'viber-after-failure'], 200),
        ]);
        $appointment = $this->appointment($master, $service, $customer, '2030-01-03 10:00:00', 'confirmed', 'ru');

        app(TriggerMessageDispatcher::class)->processDue();
        $payload = json_encode(['entry'=>[['changes'=>[['value'=>['statuses'=>[ ['id'=>'wamid.123','status'=>'failed','errors'=>[['title'=>'undeliverable']]] ]]]]]]]);
        $signature = 'sha256='.hash_hmac('sha256', $payload, 'meta-secret');
        $this->call('POST', route('webhooks.messaging.whatsapp'), [], [], [], [
            'CONTENT_TYPE'=>'application/json', 'HTTP_X_HUB_SIGNATURE_256'=>$signature,
        ], $payload)->assertOk();

        app(TriggerMessageDispatcher::class)->processDue();
        $dispatch = TriggerMessageDispatch::where('appointment_id', $appointment->id)->where('trigger', 'booking_created')->firstOrFail();
        $this->assertSame('viber', $dispatch->sent_provider);
        $this->assertSame(['failed', 'sent'], $dispatch->attempts()->orderBy('id')->pluck('status')->all());
    }

    public function test_only_super_admin_can_save_complete_multilingual_trigger_settings(): void
    {
        [$master] = $this->records();
        $adminRole = Roles::create(['name'=>'Super Admin','slug'=>'super-admin','appointment_scope'=>'all']);
        $admin = User::factory()->create(['role_id'=>$adminRole->id,'email_verified_at'=>now()]);
        $payload = ['triggers'=>[], 'priorities'=>['whatsapp'=>1,'viber'=>2,'telegram'=>3]];
        $locales = array_keys(app(SiteLocaleRegistry::class)->installedLabels());
        foreach (MessagingTriggerSetting::TRIGGERS as $trigger) {
            $template = $trigger === 'review_request'
                ? 'Rate {feedback_url}'
                : 'Booking {service} {date} {time}';
            $payload['triggers'][$trigger] = [
                'enabled'=>1,
                'timing_minutes'=>$trigger === 'appointment_reminder' ? 1440 : ($trigger === 'review_request' ? 60 : 0),
                'templates'=>array_fill_keys($locales, $template),
            ];
        }

        $this->actingAs($master)->put(route('settings.messaging-automation.update'), $payload)->assertRedirect();
        $this->assertDatabaseMissing('messaging_trigger_settings', ['trigger'=>'review_request','enabled'=>1]);
        $this->actingAs($admin)->put(route('settings.messaging-automation.update'), $payload)
            ->assertRedirect(route('settings.index').'#tab-integrations');
        $this->assertDatabaseHas('messaging_trigger_settings', ['trigger'=>'review_request','enabled'=>1,'timing_minutes'=>60]);
        $this->assertDatabaseHas('messaging_integrations', ['provider'=>'whatsapp','priority'=>1]);
    }

    private function records(): array
    {
        $role = Roles::create(['name'=>'Master','slug'=>'master','appointment_scope'=>'own','is_service_provider'=>true]);
        $master = User::factory()->create(['role_id'=>$role->id]);
        $service = Services::create(['name'=>'Massage','price'=>50,'duration_minutes'=>60,'eventColor'=>'#123456','status'=>true]);
        $customer = Customer::create(['first_name'=>'Mari','last_name'=>'Tamm','email'=>'mari@example.test','phone'=>'+37255550000','locale'=>'et']);

        return [$master, $service, $customer];
    }

    private function appointment(User $master, Services $service, Customer $customer, string $start, string $status, string $locale): Appointments
    {
        return Appointments::create([
            'customer_id'=>$customer->id, 'user_id'=>$master->id, 'service_id'=>$service->id,
            'client_name'=>$customer->first_name, 'client_lastname'=>$customer->last_name,
            'client_phone'=>$customer->phone, 'client_email'=>$customer->email, 'client_locale'=>$locale,
            'appointment_start'=>$start, 'appointment_end'=>Carbon::parse($start)->addHour(),
            'status'=>$status, 'price'=>50,
        ]);
    }
}
