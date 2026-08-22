<?php

namespace Tests\Feature;

use App\Models\Roles;
use App\Models\Services;
use App\Models\SiteSettings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ServiceImageTest extends TestCase
{
    use RefreshDatabase;

    public function test_service_image_is_validated_optimized_and_stored_as_webp(): void
    {
        Storage::fake('public');
        [$admin, $master] = $this->users();

        $this->actingAs($admin)->post(route('service.store'), [
            'name' => 'SEO Friendly Service',
            'price' => 45,
            'duration_minutes' => 60,
            'masters' => [$master->id],
            'eventColor' => '#7398E2',
            'image' => UploadedFile::fake()->image('large-service.jpg', 2400, 1600)->size(900),
        ])->assertRedirect(route('service.create'));

        $service = Services::where('name', 'SEO Friendly Service')->firstOrFail();
        $this->assertMatchesRegularExpression('#^services/seo-friendly-service-[a-z0-9]+\.webp$#', $service->image_path);
        Storage::disk('public')->assertExists($service->image_path);

        [$width, $height, $type] = getimagesize(Storage::disk('public')->path($service->image_path));
        $this->assertLessThanOrEqual(1600, $width);
        $this->assertLessThanOrEqual(1200, $height);
        $this->assertSame(IMAGETYPE_WEBP, $type);

        $this->actingAs($admin)->get(route('service.show', $service))
            ->assertOk()
            ->assertSee(__('admin_services.image_hint'));
    }

    public function test_service_image_rejects_svg_uploads(): void
    {
        Storage::fake('public');
        [$admin, $master] = $this->users();

        $this->actingAs($admin)->from(route('service.create'))->post(route('service.store'), [
            'name' => 'Unsafe image service',
            'price' => 45,
            'duration_minutes' => 60,
            'masters' => [$master->id],
            'eventColor' => '#7398E2',
            'image' => UploadedFile::fake()->createWithContent('payload.svg', '<svg><script>alert(1)</script></svg>'),
        ])->assertRedirect(route('service.create'))->assertSessionHasErrors('image');

        $this->assertDatabaseMissing('services', ['name' => 'Unsafe image service']);
    }

    public function test_service_image_accepts_small_dimensions(): void
    {
        Storage::fake('public');
        [$admin, $master] = $this->users();

        $this->actingAs($admin)->post(route('service.store'), [
            'name' => 'Small source image service',
            'price' => 30,
            'duration_minutes' => 30,
            'masters' => [$master->id],
            'eventColor' => '#7398E2',
            'image' => UploadedFile::fake()->image('small.png', 120, 80),
        ])->assertRedirect(route('service.create'));

        $service = Services::where('name', 'Small source image service')->firstOrFail();
        Storage::disk('public')->assertExists($service->image_path);
    }

    public function test_existing_service_image_is_replaced_and_persisted(): void
    {
        Storage::fake('public');
        [$admin, $master] = $this->users();
        Storage::disk('public')->put('services/old.webp', 'old image');
        $service = Services::create([
            'name' => 'Existing service',
            'price' => 40,
            'duration_minutes' => 60,
            'image_path' => 'services/old.webp',
            'eventColor' => '#7398E2',
            'status' => 1,
        ]);
        $service->users()->attach($master->id);

        $this->actingAs($admin)->put(route('service.update', $service), [
            'name' => 'Existing service',
            'price' => 40,
            'duration_minutes' => 60,
            'masters' => [$master->id],
            'eventColor' => '#7398E2',
            'status' => '1',
            'image' => UploadedFile::fake()->image('replacement.jpg', 900, 600),
        ])->assertRedirect(route('service.show', $service));

        $service->refresh();
        $this->assertNotSame('services/old.webp', $service->image_path);
        Storage::disk('public')->assertExists($service->image_path);
        Storage::disk('public')->assertMissing('services/old.webp');
        $this->actingAs($admin)->get(route('service.show', $service))
            ->assertSee($service->image_url, false);
    }

    public function test_image_visibility_switches_are_saved_and_exposed_to_booking(): void
    {
        [$admin] = $this->users();

        $this->actingAs($admin)->post(route('settings.main.update'), [
            'company_name' => 'Test company',
            'company_email' => 'company@example.test',
            'booking_template' => 'classic',
            'show_service_images' => '0',
            'show_master_images' => '0',
            'allow_customer_cancellation' => '0',
        ])->assertOk();

        $this->assertFalse(json_decode(SiteSettings::where('key', 'show_service_images')->value('payload'), true));
        $this->assertFalse(json_decode(SiteSettings::where('key', 'show_master_images')->value('payload'), true));
        $this->assertFalse(json_decode(SiteSettings::where('key', 'allow_customer_cancellation')->value('payload'), true));

        $this->get(route('serviceBooking'))
            ->assertOk()
            ->assertSee('const showServiceImages = false;', false)
            ->assertSee('const showMasterImages = false;', false);
    }

    private function users(): array
    {
        $adminRole = Roles::create(['name' => 'Super Admin']);
        $masterRole = Roles::create(['name' => 'Master']);
        $admin = User::factory()->create(['username' => '@image_admin', 'role_id' => $adminRole->id, 'email_verified_at' => now()]);
        $master = User::factory()->create(['username' => '@image_master', 'role_id' => $masterRole->id, 'email_verified_at' => now()]);

        return [$admin, $master];
    }
}
