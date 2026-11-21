<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class DemoAccountsTest extends TestCase
{
    use RefreshDatabase;

    public function test_testing_seeder_creates_idempotent_demo_accounts_that_can_login(): void
    {
        $this->seed(DatabaseSeeder::class);
        $this->seed(DatabaseSeeder::class);

        $accounts = [
            ['email' => 'admin@eduka.test', 'role' => Role::ADMIN],
            ['email' => 'pengajar@eduka.test', 'role' => Role::TEACHER],
            ['email' => 'siswa@eduka.test', 'role' => Role::STUDENT],
        ];

        foreach ($accounts as $account) {
            $user = User::query()->where('email', $account['email'])->firstOrFail();

            $this->assertSame($account['role'], $user->role);
            $this->assertTrue($user->isActive());
            $this->assertTrue(Hash::check('EdukaDemo2026!', $user->password));
            $this->assertSame(1, User::query()->where('email', $account['email'])->count());

            $this->post('/login', ['email' => $account['email'], 'password' => 'EdukaDemo2026!'])
                ->assertRedirect('/dashboard');
            $this->assertAuthenticatedAs($user);
            $this->post('/logout')->assertRedirect('/login');
        }
    }

    public function test_demo_seeder_is_disabled_when_demo_configuration_is_off(): void
    {
        config(['cbt.demo.enabled' => false]);

        $this->seed(DatabaseSeeder::class);

        $this->assertDatabaseMissing('users', ['email' => 'admin@eduka.test']);
        $this->assertDatabaseMissing('users', ['email' => 'pengajar@eduka.test']);
        $this->assertDatabaseMissing('users', ['email' => 'siswa@eduka.test']);
    }
}
