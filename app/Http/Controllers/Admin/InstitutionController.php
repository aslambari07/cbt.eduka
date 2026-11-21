<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\InstitutionRequest;
use App\Models\Institution;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class InstitutionController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('viewAny', Institution::class);
        $institutions = Institution::when($request->filled('search'), fn ($q) => $q->where('name', 'like', '%'.$request->string('search').'%')->orWhere('code', 'like', '%'.$request->string('search').'%'))->latest()->paginate(15)->withQueryString();

        return view('admin.institutions.index', compact('institutions'));
    }

    public function create(): View
    {
        $this->authorize('create', Institution::class);

        return view('admin.institutions.form', ['institution' => null]);
    }

    public function store(InstitutionRequest $request): RedirectResponse
    {
        Institution::create($request->validated());

        return redirect()->route('admin.institutions.index')->with('status', 'Institusi berhasil dibuat.');
    }

    public function edit(Institution $institution): View
    {
        $this->authorize('update', $institution);

        return view('admin.institutions.form', compact('institution'));
    }

    public function update(InstitutionRequest $request, Institution $institution): RedirectResponse
    {
        $institution->update($request->validated());

        return redirect()->route('admin.institutions.index')->with('status', 'Institusi berhasil diperbarui.');
    }

    public function destroy(Institution $institution): RedirectResponse
    {
        $this->authorize('delete', $institution);
        $institution->delete();

        return redirect()->route('admin.institutions.index')->with('status', 'Institusi diarsipkan.');
    }
}
