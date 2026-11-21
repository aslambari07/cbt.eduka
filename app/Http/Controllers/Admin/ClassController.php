<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SchoolClassRequest;
use App\Models\AcademicYear;
use App\Models\Institution;
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
        $classes = SchoolClass::with(['institution', 'homeroomTeacher'])->when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%'))->latest()->paginate(15)->withQueryString();

        return view('admin.classes.index', compact('classes'));
    }

    public function create(): View
    {
        $this->authorize('create', SchoolClass::class);

        return view('admin.classes.form', $this->formData(null));
    }

    public function store(SchoolClassRequest $request): RedirectResponse
    {
        SchoolClass::create($request->validated());

        return redirect()->route('admin.classes.index')->with('status', 'Kelas berhasil dibuat.');
    }

    public function edit(SchoolClass $class): View
    {
        $this->authorize('update', $class);

        return view('admin.classes.form', $this->formData($class));
    }

    public function update(SchoolClassRequest $request, SchoolClass $class): RedirectResponse
    {
        $class->update($request->validated());

        return redirect()->route('admin.classes.index')->with('status', 'Kelas berhasil diperbarui.');
    }

    public function destroy(SchoolClass $class): RedirectResponse
    {
        $this->authorize('delete', $class);
        $class->delete();

        return redirect()->route('admin.classes.index')->with('status', 'Kelas diarsipkan.');
    }

    private function formData(?SchoolClass $class): array
    {
        return ['class' => $class, 'institutions' => Institution::orderBy('name')->get(), 'years' => AcademicYear::orderByDesc('is_current')->orderBy('name')->get(), 'teachers' => User::where('role', 'teacher')->where('status', 'active')->orderBy('name')->get()];
    }
}
