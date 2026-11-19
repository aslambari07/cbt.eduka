<?php

namespace Tests\Feature;

use App\Enums\AccountStatus;
use App\Enums\Role;
use App\Models\Institution;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Tests\TestCase;

class OrganizationAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_users_have_typed_roles_and_statuses(): void
    {
        $user = User::factory()->create([
            'role' => Role::TEACHER,
            'status' => AccountStatus::ACTIVE,
        ]);

        $this->assertSame(Role::TEACHER, $user->role);
        $this->assertSame(AccountStatus::ACTIVE, $user->status);
        $this->assertTrue($user->hasRole(Role::TEACHER));
        $this->assertTrue($user->isActive());
    }

    public function test_organization_relationships_persist_with_unique_membership(): void
    {
        $institution = Institution::factory()->create(['code' => 'SMAN8']);
        $class = SchoolClass::factory()->create(['institution_id' => $institution->id]);
        $subject = Subject::factory()->create(['institution_id' => $institution->id]);
        $student = User::factory()->create([
            'role' => Role::STUDENT,
            'institution_id' => $institution->id,
        ]);

        $class->students()->attach($student->id);
        $class->students()->syncWithoutDetaching([$student->id]);

        $this->assertCount(1, $class->fresh()->students);
        $this->assertTrue($institution->fresh()->classes->contains($class));
        $this->assertTrue($institution->fresh()->subjects->contains($subject));
    }

    public function test_teacher_can_only_update_assigned_class(): void
    {
        $institution = Institution::factory()->create();
        $teacher = User::factory()->create([
            'role' => Role::TEACHER,
            'institution_id' => $institution->id,
        ]);
        $assigned = SchoolClass::factory()->create(['institution_id' => $institution->id]);
        $unassigned = SchoolClass::factory()->create(['institution_id' => $institution->id]);

        $assigned->teachers()->attach($teacher->id);

        Gate::forUser($teacher)->allows('update', $assigned);
        $this->assertTrue(Gate::forUser($teacher)->allows('update', $assigned));
        $this->assertFalse(Gate::forUser($teacher)->allows('update', $unassigned));
    }
}
