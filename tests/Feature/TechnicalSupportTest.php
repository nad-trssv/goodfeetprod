<?php

namespace Tests\Feature;

use App\Mail\TechnicalSupportMail;
use App\Models\Roles;
use App\Models\SiteSettings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class TechnicalSupportTest extends TestCase
{
    use RefreshDatabase;

    public function test_every_authenticated_employee_can_open_support_and_send_domain_email(): void
    {
        Mail::fake();
        config(['app.url' => 'https://goodfeet.ee']);
        $employee = $this->employee();
        $this->setting('technical_support_sender_email', 'info@goodfeet.ee');
        $this->setting('company_name', 'GoodFeet OÜ');

        $this->actingAs($employee)->get(route('technical-support.create'))
            ->assertOk()
            ->assertSee(__('admin_support.title'))
            ->assertSee(__('admin_nav.technical_support'));

        $this->actingAs($employee)->post(route('technical-support.store'), [
            'category' => 'problem',
            'subject' => 'Calendar is not loading',
            'message' => 'The calendar remains empty after choosing a specialist.',
            'page_url' => 'http://localhost/calendar',
        ])->assertRedirect(route('technical-support.create'))
            ->assertSessionHas('support_success');

        Mail::assertSent(TechnicalSupportMail::class, function (TechnicalSupportMail $mail) use ($employee) {
            $envelope = $mail->envelope();

            return $mail->hasTo('quickcodeou@gmail.com')
                && $envelope->from?->address === 'info@goodfeet.ee'
                && $envelope->replyTo[0]->address === $employee->email
                && $mail->pageUrl === 'http://localhost/calendar';
        });
        $this->assertDatabaseHas('activity_logs', [
            'event' => 'technical_support.sent',
            'actor_id' => $employee->id,
        ]);
    }

    public function test_public_or_foreign_sender_domain_is_rejected_server_side(): void
    {
        Mail::fake();
        config(['app.url' => 'https://goodfeet.ee']);
        $employee = $this->employee();

        foreach (['company@gmail.com', 'info@another-company.ee'] as $sender) {
            $this->setting('technical_support_sender_email', $sender);
            $this->actingAs($employee)->post(route('technical-support.store'), [
                'category' => 'question',
                'subject' => 'A valid support question',
                'message' => 'This message is long enough for validation.',
            ])->assertSessionHasErrors('support');
        }

        Mail::assertNothingSent();
    }

    public function test_support_routes_are_not_available_to_guests(): void
    {
        $this->get(route('technical-support.create'))->assertRedirect(route('login'));
        $this->post(route('technical-support.store'))->assertRedirect(route('login'));
    }

    private function employee(): User
    {
        $role = Roles::create([
            'name' => 'Master',
            'slug' => 'master',
            'appointment_scope' => 'own',
            'is_service_provider' => true,
        ]);

        return User::factory()->create([
            'role_id' => $role->id,
            'email_verified_at' => now(),
            'locale' => 'ru',
        ]);
    }

    private function setting(string $key, string $value): void
    {
        SiteSettings::withTrashed()->updateOrCreate(
            ['key' => $key],
            ['group' => 'company', 'payload' => json_encode($value), 'deleted_at' => null],
        );
    }
}
