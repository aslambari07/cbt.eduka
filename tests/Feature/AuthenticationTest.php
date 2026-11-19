<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_view_the_login_page(): void
    {
        $this->get('/login')
            ->assertOk()
            ->assertSee('Masuk ke CBT.EDUKA')
            ->assertSee('Alamat email');
    }

    public function test_guest_is_redirected_from_dashboard_to_login(): void
    {
        $this->get('/dashboard')->assertRedirect('/login');
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create([
            'email' => 'nabila@example.test',
            'password' => Hash::make('password123'),
        ]);

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password123',
            'remember' => true,
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticatedAs($user);

        $this->post('/logout')->assertRedirect('/login');
        $this->assertGuest();
    }

    public function test_invalid_credentials_return_a_validation_error(): void
    {
        User::factory()->create(['email' => 'nabila@example.test']);

        $this->from('/login')
            ->post('/login', [
                'email' => 'nabila@example.test',
                'password' => 'wrong-password',
            ])
            ->assertRedirect('/login')
            ->assertSessionHasErrors('email');
    }

    public function test_guest_can_register_an_account(): void
    {
        $this->get('/register')
            ->assertOk()
            ->assertSee('Daftar di CBT.EDUKA');

        $this->post('/register', [
            'name' => 'Nabila Aulia',
            'email' => 'nabila.register@example.test',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('/dashboard');

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'nabila.register@example.test']);
    }
}
