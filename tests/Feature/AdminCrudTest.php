<?php

namespace Tests\Feature;

use App\Enums\Role;
use App\Models\Institution;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminCrudTest extends TestCase
{
    use RefreshDatabase;

    public function test_non_admin_cannot_access_admin_resources(): void
    {
        $teacher = User::factory()->create(['role' => Role::TEACHER]);

        $response = $this->actingAs($teacher)->get(route('admin.institutions.index'));

        $response->assertForbidden();
    }

    public function test_admin_can_create_update_and_archive_an_institution(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);

        $this->actingAs($admin)->get(route('admin.institutions.index'))->assertOk()->assertSee('Institusi');
        $this->actingAs($admin)->get(route('admin.institutions.create'))->assertOk()->assertSee('Tambah institusi');

        $create = $this->actingAs($admin)->post(route('admin.institutions.store'), [
            'name' => 'SMA Negeri 99',
            'code' => 'SMAN99',
            'contact_email' => 'admin@sman99.test',
            'address' => 'Bandung',
            'status' => 'active',
        ]);
        $institution = Institution::query()->where('code', 'SMAN99')->firstOrFail();
        $create->assertRedirect(route('admin.institutions.index'));
        $this->assertModelExists($institution);

        $this->actingAs($admin)->put(route('admin.institutions.update', $institution), [
            'name' => 'SMA Negeri 99 Updated',
            'code' => 'SMAN99',
            'contact_email' => 'updated@sman99.test',
            'address' => 'Bandung Barat',
            'status' => 'active',
        ])->assertRedirect(route('admin.institutions.index'));
        $this->assertDatabaseHas('institutions', ['id' => $institution->id, 'name' => 'SMA Negeri 99 Updated']);

        $this->actingAs($admin)->delete(route('admin.institutions.destroy', $institution))->assertRedirect(route('admin.institutions.index'));
        $this->assertSoftDeleted('institutions', ['id' => $institution->id]);
    }

    public function test_admin_can_create_subject_class_and_user_with_persisted_relationships(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $institution = Institution::factory()->create();

        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk()->assertSee('Pengguna');
        $this->actingAs($admin)->get(route('admin.subjects.index'))->assertOk()->assertSee('Mata pelajaran');
        $this->actingAs($admin)->get(route('admin.classes.index'))->assertOk()->assertSee('Kelas');

        $this->actingAs($admin)->post(route('admin.subjects.store'), [
            'institution_id' => $institution->id,
            'name' => 'Kimia',
            'code' => 'KIM-01',
            'description' => 'Dasar kimia',
            'status' => 'active',
        ])->assertRedirect(route('admin.subjects.index'));

        $this->actingAs($admin)->post(route('admin.classes.store'), [
            'institution_id' => $institution->id,
            'name' => 'XII IPA 3',
            'level' => 'XII',
            'status' => 'active',
        ])->assertRedirect(route('admin.classes.index'));

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'Guru Kimia',
            'email' => 'guru.kimia@test.local',
            'password' => 'password123',
            'role' => 'teacher',
            'status' => 'active',
            'institution_id' => $institution->id,
        ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('subjects', ['institution_id' => $institution->id, 'code' => 'KIM-01']);
        $this->assertDatabaseHas('classes', ['institution_id' => $institution->id, 'name' => 'XII IPA 3']);
        $this->assertDatabaseHas('users', ['institution_id' => $institution->id, 'email' => 'guru.kimia@test.local', 'role' => 'teacher']);
    }

    public function test_admin_can_update_platform_settings_and_non_admin_cannot(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        $teacher = User::factory()->create(['role' => Role::TEACHER]);

        $this->actingAs($teacher)->get(route('admin.settings.edit'))->assertForbidden();
        $this->actingAs($admin)->get(route('admin.settings.edit'))->assertOk()->assertSee('Pengaturan platform');
        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'platform_name' => 'CBT.EDUKA Pro',
            'platform_tagline' => 'Ujian digital yang terukur.',
            'default_duration_minutes' => 45,
            'default_passing_grade' => 80,
            'results_publication' => 'after_review',
            'maintenance_mode' => true,
        ])->assertRedirect();

        $this->assertDatabaseHas('system_settings', ['setting_key' => 'platform_name']);
        $this->assertDatabaseHas('audit_logs', ['action' => 'system_settings.updated', 'actor_id' => $admin->id]);
    }

    public function test_admin_can_export_users_as_csv(): void
    {
        $admin = User::factory()->create(['role' => Role::ADMIN]);
        User::factory()->create(['name' => 'CSV Student', 'email' => 'csv-student@example.test', 'role' => Role::STUDENT]);

        $response = $this->actingAs($admin)->get(route('admin.users.export'));
        $response->assertOk()->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('CSV Student', $response->streamedContent());
    }
}
