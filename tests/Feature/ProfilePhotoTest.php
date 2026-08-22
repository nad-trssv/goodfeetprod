<?php

namespace Tests\Feature;

use App\Models\Roles;
use App\Models\Services;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ProfilePhotoTest extends TestCase
{
    use RefreshDatabase;

    public function test_profile_photo_is_persisted_and_exposed_on_booking_page(): void
    {
        Storage::fake('public');
        $role = Roles::create(['name' => 'Super Admin']);
        $user = User::factory()->create([
            'username' => '@profile_admin',
            'role_id' => $role->id,
            'phone' => '+37250000001',
            'email_verified_at' => now(),
        ]);
        $service = Services::create([
            'name' => 'Profile photo service',
            'price' => 40,
            'duration_minutes' => 60,
            'eventColor' => '#7398E2',
            'status' => 1,
        ]);
        $service->users()->attach($user->id);

        $this->actingAs($user)->post(route('profile.ajax.update'), [
            'user_id' => $user->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'phone' => $user->phone,
            'date_birthday' => '1990-01-01',
            'professional_titles' => [
                'et' => 'Jalahoolduse spetsialist',
                'ru' => 'Мастер по педикюру PRO',
            ],
            'profile_photo_path' => UploadedFile::fake()->image('avatar.jpg', 600, 600),
        ])->assertOk();

        $user->refresh();
        $this->assertNotNull($user->profile_photo_path);
        $this->assertSame('Мастер по педикюру PRO', $user->professionalTitle('ru'));
        Storage::disk('public')->assertExists($user->profile_photo_path);
        $this->actingAs($user)->get(route('profile.index'))
            ->assertOk()
            ->assertSee('Специализация мастера');
        $this->actingAs($user)->get(route('member.edit', $user))
            ->assertOk()
            ->assertSee('Должности на языках');
        $this->get(route('serviceBooking'))
            ->assertOk()
            ->assertSee(basename($user->profile_photo_path))
            ->assertSee(json_encode('Мастер по педикюру PRO', JSON_THROW_ON_ERROR), false);
    }

    public function test_profile_cannot_use_another_users_phone_or_id(): void
    {
        $role = Roles::create(['name' => 'Super Admin']);
        $user = User::factory()->create(['username' => '@first', 'role_id' => $role->id, 'phone' => '+37250000001']);
        $other = User::factory()->create(['username' => '@second', 'role_id' => $role->id, 'phone' => '+37250000002']);

        $this->actingAs($user)->post(route('profile.ajax.update'), [
            'user_id' => $other->id,
            'name' => $user->name,
            'username' => $user->username,
            'email' => $user->email,
            'phone' => $other->phone,
        ])->assertSessionHasErrors(['user_id', 'phone']);
    }
}
