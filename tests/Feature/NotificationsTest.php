<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

class NotificationsTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_read_and_mark_all_own_notifications(): void
    {
        $user = User::factory()->create(['role' => Role::STUDENT]);
        $notification = $user->notifications()->create([
            'id' => (string) Str::uuid(),
            'type' => 'test.notification',
            'data' => ['title' => 'Ujian baru', 'message' => 'Silakan mulai.', 'url' => route('notifications.index')],
        ]);

        $this->actingAs($user)->get(route('notifications.index'))->assertOk()->assertSee('Ujian baru');
        $this->actingAs($user)->get(route('notifications.read', $notification->id))->assertRedirect(route('notifications.index'));
        $this->assertDatabaseHas('notifications', ['id' => $notification->id, 'read_at' => now()]);

        $second = $user->notifications()->create(['id' => (string) Str::uuid(), 'type' => 'test.notification', 'data' => ['title' => 'Kedua']]);
        $this->actingAs($user)->post(route('notifications.read-all'))->assertRedirect();
        $this->assertDatabaseHas('notifications', ['id' => $second->id]);
        $this->assertNotNull($second->fresh()->read_at);
    }

    public function test_user_cannot_mark_another_users_notification_as_read(): void
    {
        $owner = User::factory()->create(['role' => Role::STUDENT]);
        $other = User::factory()->create(['role' => Role::STUDENT]);
        $notification = $owner->notifications()->create(['id' => (string) Str::uuid(), 'type' => 'test.notification', 'data' => ['title' => 'Private']]);

        $this->actingAs($other)->get(route('notifications.read', $notification->id))->assertNotFound();
    }
}
