<?php

namespace Tests\Feature;

use App\Models\Appointments;
use App\Models\Customer;
use App\Models\Roles;
use App\Models\Services;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Lang;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Tests\TestCase;

class AdminNavigationLocalizationSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_locale_controls_the_menu_and_secondary_sections_are_collapsed(): void
    {
        $master = $this->staff('Master', '@localized_master', 'ru');

        $this->actingAs($master)->get(route('admin.search'))
            ->assertOk()
            ->assertSee('Мой календарь')
            ->assertSee('Управление')
            ->assertSee('id="adminNavManagement"', false)
            ->assertSee('id="adminNavProfile"', false)
            ->assertSee('aria-controls="adminNavManagement"', false)
            ->assertSee('aria-expanded="false"', false);
    }

    public function test_employee_can_save_own_locale_and_admin_can_change_an_employee_locale(): void
    {
        $admin = $this->staff('Admin', '@locale_admin', 'et');
        $master = $this->staff('Master', '@locale_employee', 'et');

        $this->actingAs($master)->postJson(route('profile.ajax.update'), [
            'user_id' => $master->id,
            'name' => $master->name,
            'username' => $master->username,
            'email' => $master->email,
            'phone' => $master->phone,
            'date_birthday' => null,
            'locale' => 'ru',
        ])->assertOk();
        $this->assertSame('ru', $master->fresh()->locale);

        $this->actingAs($admin)->putJson(route('member.update', $master), [
            'name' => $master->name,
            'username' => $master->username,
            'email' => $master->email,
            'phone' => $master->phone,
            'role_id' => $master->role_id,
            'date_birthday' => null,
            'employment_started_at' => null,
            'locale' => 'en',
        ])->assertOk();
        $this->assertSame('en', $master->fresh()->locale);
    }

    public function test_admin_translation_catalogs_have_the_same_keys_in_all_supported_languages(): void
    {
        $russianFiles = collect(glob(lang_path('ru/admin_*.php')))
            ->map(fn (string $path) => basename($path))
            ->sort()
            ->values();

        foreach ($russianFiles as $file) {
            $russian = $this->translationKeys(require lang_path('ru/'.$file));

            foreach (['en', 'et'] as $locale) {
                $path = lang_path($locale.'/'.$file);
                $this->assertFileExists($path, "Missing {$locale}/{$file}");
                $this->assertSame($russian, $this->translationKeys(require $path), "Translation keys differ in {$locale}/{$file}");
            }
        }
    }

    public function test_new_staff_default_locale_is_russian(): void
    {
        $role = Roles::firstOrCreate(['name' => 'Master']);
        $staff = User::factory()->create([
            'role_id' => $role->id,
            'phone' => '+372'.fake()->unique()->numerify('55######'),
        ]);

        $this->assertSame('ru', $staff->fresh()->locale);
    }

    public function test_static_admin_translation_references_exist_in_every_supported_language(): void
    {
        $files = new RecursiveIteratorIterator(new RecursiveDirectoryIterator(resource_path('views/admin')));
        $keys = [];

        foreach ($files as $file) {
            if (! $file->isFile() || ! str_ends_with($file->getFilename(), '.blade.php')) {
                continue;
            }

            preg_match_all("/__\\(\\s*['\"]([^'\"]+)['\"]/", file_get_contents($file->getPathname()), $matches);
            array_push($keys, ...$matches[1]);
        }

        foreach (array_unique($keys) as $key) {
            if (str_ends_with($key, '.') || str_ends_with($key, '_')) {
                continue;
            }
            foreach (array_keys(config('supported_locales')) as $locale) {
                $this->assertTrue(Lang::has($key, $locale, false), "Missing {$locale} translation: {$key}");
            }
        }
    }

    public function test_global_search_groups_results_and_keeps_master_inside_own_customer_scope(): void
    {
        $first = $this->staff('Master', '@search_first', 'en');
        $second = $this->staff('Master', '@search_second', 'en');
        $service = Services::create([
            'name' => 'Signature manicure',
            'price' => 50,
            'duration_minutes' => 60,
            'eventColor' => '#334455',
            'status' => true,
        ]);
        $service->users()->attach([$first->id, $second->id]);
        $own = Customer::create(['first_name'=>'Searchable','last_name'=>'Own','email'=>'own-search@example.test','phone'=>'+37255551001','locale'=>'en']);
        $foreign = Customer::create(['first_name'=>'Searchable','last_name'=>'Foreign','email'=>'foreign-search@example.test','phone'=>'+37255551002','locale'=>'en']);
        $this->appointment($first, $service, $own, '2030-03-01 10:00:00');
        $this->appointment($second, $service, $foreign, '2030-03-01 12:00:00');

        $response = $this->actingAs($first)->getJson(route('admin.search', ['q' => 'Searchable']))
            ->assertOk()
            ->json('html');

        $this->assertStringContainsString('Searchable Own', $response);
        $this->assertStringNotContainsString('Searchable Foreign', $response);

        $serviceResponse = $this->actingAs($first)->get(route('admin.search', ['q' => 'Signature']))
            ->assertOk();
        $serviceResponse->assertSee('Signature manicure');
    }

    private function staff(string $roleName, string $username, string $locale): User
    {
        $role = Roles::firstOrCreate(['name' => $roleName]);

        return User::factory()->create([
            'username' => $username,
            'role_id' => $role->id,
            'phone' => '+372'.fake()->unique()->numerify('55######'),
            'locale' => $locale,
            'email_verified_at' => now(),
        ]);
    }

    private function appointment(User $master, Services $service, Customer $customer, string $start): Appointments
    {
        return Appointments::create([
            'customer_id' => $customer->id,
            'user_id' => $master->id,
            'service_id' => $service->id,
            'client_name' => $customer->first_name,
            'client_lastname' => $customer->last_name,
            'client_email' => $customer->email,
            'client_phone' => $customer->phone,
            'price' => 50,
            'appointment_start' => $start,
            'appointment_end' => \Carbon\Carbon::parse($start)->addHour(),
        ]);
    }

    /** @return list<string> */
    private function translationKeys(array $translations, string $prefix = ''): array
    {
        $keys = [];

        foreach ($translations as $key => $value) {
            $path = $prefix === '' ? (string) $key : $prefix.'.'.$key;
            if (is_array($value)) {
                array_push($keys, ...$this->translationKeys($value, $path));
            } else {
                $keys[] = $path;
            }
        }

        sort($keys);

        return $keys;
    }
}
