<?php

namespace Database\Seeders;

use App\Enums\AccountStatus;
use App\Enums\Role;
use App\Models\Institution;
use App\Models\SchoolClass;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $institution = Institution::query()->updateOrCreate(
            ['code' => 'DEMO-EDUKA'],
            [
                'name' => 'SMA Demo CBT.EDUKA',
                'contact_email' => 'admin@cbt-eduka.test',
                'address' => 'Bandung',
                'status' => 'active',
            ],
        );

        $admin = User::query()->updateOrCreate(
            ['email' => 'admin@cbt-eduka.test'],
            ['name' => 'Arif Dharma', 'role' => Role::ADMIN, 'status' => AccountStatus::ACTIVE, 'institution_id' => $institution->id, 'password' => Hash::make('password123')],
        );
        $teacher = User::query()->updateOrCreate(
            ['email' => 'rina@cbt-eduka.test'],
            ['name' => 'Rina Permata', 'role' => Role::TEACHER, 'status' => AccountStatus::ACTIVE, 'institution_id' => $institution->id, 'password' => Hash::make('password123')],
        );
        $student = User::query()->updateOrCreate(
            ['email' => 'nabila@cbt-eduka.test'],
            ['name' => 'Nabila Aulia', 'role' => Role::STUDENT, 'status' => AccountStatus::ACTIVE, 'institution_id' => $institution->id, 'password' => Hash::make('password123')],
        );

        $teacher->teacherProfile()->updateOrCreate([], ['employee_number' => 'T-0001', 'specialization' => 'Matematika']);
        $student->studentProfile()->updateOrCreate([], ['student_number' => 'S-0001', 'grade' => 'XII', 'major' => 'IPA']);

        $math = Subject::query()->updateOrCreate(
            ['institution_id' => $institution->id, 'code' => 'MTK-XII'],
            ['name' => 'Matematika', 'description' => 'Limit, turunan, dan persamaan kuadrat.', 'status' => 'active'],
        );

        $class = SchoolClass::query()->updateOrCreate(
            ['institution_id' => $institution->id, 'name' => 'XII IPA 2'],
            ['level' => 'XII', 'status' => 'active', 'homeroom_teacher_id' => $teacher->id],
        );

        $class->subjects()->syncWithoutDetaching([$math->id]);
        $class->teachers()->syncWithoutDetaching([$teacher->id => ['subject_id' => $math->id]]);
        $class->students()->syncWithoutDetaching([$student->id => ['joined_at' => now()]]);

        unset($admin);
    }
}
