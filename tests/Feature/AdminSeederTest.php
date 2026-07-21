<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_a_verified_admin_and_is_idempotent(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $admin = User::where('email', 'admin@localhost.test')->firstOrFail();

        $this->assertSame('Admin', $admin->role->name);
        $this->assertNotNull($admin->email_verified_at);
        $this->assertTrue(Hash::check('ChangeMe123!', $admin->password));
        $this->assertSame(1, User::where('email', 'admin@localhost.test')->count());
    }
}
