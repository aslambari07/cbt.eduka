<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\TeacherClassStudentRequest;
use App\Models\SchoolClass;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ClassController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', SchoolClass::class);
        $classes = SchoolClass::query()->with('institution')->withCount('students')->when($request->user()->hasRole('teacher'), fn ($query) => $query->whereHas('teachers', fn ($teachers) => $teachers->whereKey($request->user()->id)))->latest()->paginate(12);

        return view('teacher.classes.index', compact('classes'));
    }

    public function show(Request $request, SchoolClass $class): View
    {
        $this->authorize('view', $class);
        $class->load(['institution', 'students', 'subjects', 'homeroomTeacher']);

        return view('teacher.classes.show', compact('class'));
    }

    public function addStudent(TeacherClassStudentRequest $request, SchoolClass $class): RedirectResponse
    {
        $student = User::query()->whereKey($request->validated('student_id'))->where('institution_id', $class->institution_id)->where('role', 'student')->where('status', 'active')->firstOrFail();
        $class->students()->syncWithoutDetaching([$student->id => ['joined_at' => now()]]);

        return back()->with('status', 'Peserta berhasil ditambahkan ke kelas.');
    }

    public function removeStudent(Request $request, SchoolClass $class, User $student): RedirectResponse
    {
        $this->authorize('update', $class);
        abort_unless($student->institution_id === $class->institution_id && $student->hasRole('student'), 404);
        $class->students()->detach($student->id);

        return back()->with('status', 'Peserta dikeluarkan dari kelas.');
    }
}
