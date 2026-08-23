<?php

namespace Tests\Feature;

use App\Models\Roles;
use App\Models\Services;
use App\Models\SiteLocale;
use App\Models\SiteSettings;
use App\Models\SiteTranslation;
use App\Models\User;
use App\Services\Crm\CrmChatSettings;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Tests\TestCase;

class SiteLocalizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_new_language_inherits_frontend_services_staff_and_localized_settings(): void
    {
        $admin = $this->admin();
        $service = Services::create([
            'name' => 'Põhiteenus',
            'price' => 40,
            'duration_minutes' => 60,
            'eventColor' => '#654321',
            'status' => true,
        ]);
        $service->translations()->create([
            'locale' => 'et',
            'name' => 'Põhiteenus',
            'short_description' => 'Kirjeldus',
        ]);
        $master = User::factory()->create(['professional_titles' => ['et' => 'Küünetehnik']]);
        SiteSettings::updateOrCreate(['key' => 'company_short_description'], [
            'group' => 'company',
            'payload' => json_encode(['et' => '<p>Ettevõte</p>'], JSON_UNESCAPED_UNICODE),
        ]);

        $this->actingAs($admin)->post(route('settings.localization.languages.store'), ['code' => 'fi'])
            ->assertRedirect(route('settings.index').'#tab-localization')
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('site_locales', ['code' => 'fi', 'enabled' => false, 'is_default' => false]);
        $this->assertDatabaseHas('site_translations', ['locale' => 'fi', 'translation_key' => 'msg.services', 'is_inherited' => true]);
        $this->assertDatabaseHas('service_translations', ['service_id' => $service->id, 'locale' => 'fi', 'name' => 'Põhiteenus', 'is_inherited' => true]);
        $this->assertSame('Küünetehnik', $master->fresh()->professional_titles['fi']);
        $this->assertSame('<p>Ettevõte</p>', json_decode(SiteSettings::where('key', 'company_short_description')->value('payload'), true)['fi']);
        $this->assertArrayHasKey('fi', json_decode(SiteSettings::where('key', 'crm_chat_welcome_message')->value('payload'), true));
    }

    public function test_translation_editor_preserves_placeholders_and_public_locale_uses_database_text(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('settings.localization.languages.store'), ['code' => 'fi'])->assertRedirect();
        $locale = SiteLocale::findOrFail('fi');

        $placeholderKey = SiteTranslation::where('locale', 'fi')
            ->get()
            ->first(fn (SiteTranslation $translation) => preg_match('/:[A-Za-z_][A-Za-z0-9_]*/', $translation->value) === 1)?->translation_key;
        if ($placeholderKey) {
            $this->actingAs($admin)->put(route('settings.localization.translations.update', $locale), [
                'translation_key' => $placeholderKey,
                'value' => 'Translation without required variables',
            ])->assertSessionHasErrors('value');
        }

        $this->actingAs($admin)->put(route('settings.localization.translations.update', $locale), [
            'translation_key' => 'msg.services',
            'value' => 'Palvelut',
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->actingAs($admin)->put(route('settings.localization.languages.update', $locale), ['enabled' => 1])
            ->assertRedirect();

        Auth::forgetGuards();
        $this->flushSession();
        $this->get(route('lang.change', 'fi'))->assertRedirect()->assertSessionHas('locale', 'fi');
        $this->get(route('home'))->assertOk()->assertSee('Palvelut');
        $this->assertDatabaseHas('site_translations', [
            'locale' => 'fi',
            'translation_key' => 'msg.services',
            'value' => 'Palvelut',
            'is_inherited' => false,
        ]);
    }

    public function test_public_language_switch_overrides_staff_locale_without_changing_back_office_language(): void
    {
        $admin = $this->admin();

        $this->actingAs($admin)
            ->from(route('home'))
            ->get(route('lang.change', 'en'))
            ->assertRedirect(route('home'))
            ->assertSessionHas('locale', 'en');

        $this->get(route('home'))->assertOk()->assertSee('Services');
        $this->get(route('dashboard'))->assertOk()->assertSee('Панель управления');
        $this->assertSame('ru', $admin->fresh()->locale);
    }

    public function test_translation_editor_can_show_only_untranslated_or_translated_rows(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->post(route('settings.localization.languages.store'), ['code' => 'fi'])->assertRedirect();
        $locale = SiteLocale::findOrFail('fi');

        $this->actingAs($admin)->get(route('settings.localization.translations.index', [
            'locale' => 'fi',
            'status' => 'untranslated',
            'search' => 'msg.services',
        ]))->assertOk()->assertSee('msg.services');

        $this->actingAs($admin)->put(route('settings.localization.translations.update', $locale), [
            'translation_key' => 'msg.services',
            'value' => 'Palvelut',
        ])->assertRedirect();

        $this->actingAs($admin)->get(route('settings.localization.translations.index', [
            'locale' => 'fi',
            'status' => 'translated',
            'search' => 'msg.services',
        ]))->assertOk()->assertSee('Palvelut');

        $this->actingAs($admin)->get(route('settings.localization.translations.index', [
            'locale' => 'fi',
            'status' => 'untranslated',
            'search' => 'msg.services',
        ]))->assertOk()->assertDontSee('<code class="text-break">msg.services</code>', false);
    }

    public function test_chat_texts_are_localized_and_widget_uses_public_site_accent(): void
    {
        SiteSettings::where('key', 'crm_chat_enabled')->update(['payload' => json_encode(true)]);
        SiteSettings::where('key', 'crm_chat_title')->update([
            'payload' => json_encode(['et' => 'Vestlus', 'ru' => 'Чат', 'en' => 'Chat'], JSON_UNESCAPED_UNICODE),
        ]);
        SiteSettings::where('key', 'crm_chat_welcome_message')->update([
            'payload' => json_encode(['et' => 'Tere!', 'ru' => 'Здравствуйте!', 'en' => 'Hello!'], JSON_UNESCAPED_UNICODE),
        ]);

        App::setLocale('ru');
        $state = app(CrmChatSettings::class)->all();
        $this->assertSame('Чат', $state['title']);
        $this->assertSame('Здравствуйте!', $state['welcome_message']);

        $this->withSession(['locale' => 'ru'])->get(route('home'))
            ->assertOk()
            ->assertSee('var(--secondary,#6D5149)', false)
            ->assertDontSee('var(--brand-accent,#3874ff)', false);
    }

    public function test_default_language_cannot_be_disabled_and_localization_is_super_admin_only(): void
    {
        $admin = $this->admin();
        $default = SiteLocale::where('is_default', true)->firstOrFail();

        $this->actingAs($admin)->put(route('settings.localization.languages.update', $default), ['enabled' => 0])
            ->assertSessionHasErrors('enabled');

        $employeeRole = Roles::create(['name' => 'Employee', 'slug' => 'employee', 'appointment_scope' => 'all']);
        $employee = User::factory()->create(['role_id' => $employeeRole->id, 'email_verified_at' => now()]);
        $this->actingAs($employee)->post(route('settings.localization.languages.store'), ['code' => 'fi'])
            ->assertRedirect(route('home'));
        $this->assertDatabaseMissing('site_locales', ['code' => 'fi']);
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
            'username' => '@localization_admin',
            'role_id' => $role->id,
            'email_verified_at' => now(),
            'locale' => 'ru',
        ]);
    }
}
