<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Tests\TestCase;

class LandingAndLogoutTest extends TestCase
{
    public function test_guest_can_view_the_public_landing_page(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Ujian Online yang')
            ->assertSee('Jujur, Cepat,')
            ->assertSee('Mulai sekarang')
            ->assertSee('Jelajahi fitur')
            ->assertSee('8 tipe soal')
            ->assertSee('Pertanyaan tentang platform.')
            ->assertSee(route('login'))
            ->assertSee(route('register'));
    }

    public function test_authenticated_landing_page_shows_dashboard_and_logout_actions(): void
    {
        $user = User::factory()->teacher()->create();

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('Buka dashboard')
            ->assertSee('Keluar')
            ->assertSee('data-landing-menu-toggle', false);
    }

    public function test_each_role_can_logout_and_cannot_reopen_the_dashboard(): void
    {
        foreach ([Role::ADMIN, Role::TEACHER, Role::STUDENT] as $role) {
            $user = User::factory()->state(['role' => $role])->create();

            $privateResponse = $this->actingAs($user)->get(route('dashboard'));

            $this->assertStringContainsString('no-store', (string) $privateResponse->headers->get('Cache-Control'));
            $privateResponse->assertHeader('Pragma', 'no-cache');

            $this->get('/logout')->assertStatus(405);

            $this->post(route('logout'))->assertRedirect('/login');
            $this->assertGuest();

            $this->get(route('dashboard'))->assertRedirect('/login');
        }
    }
}
