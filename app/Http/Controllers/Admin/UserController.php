<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\AdminUserRequest;
use App\Models\Institution;
use App\Models\User;
use App\Services\CsvExportService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserController extends Controller
{
    public function __construct(private readonly CsvExportService $csv) {}

    public function index(Request $request): View
    {
        $this->authorize('viewAny', User::class);
        $users = User::with('institution')->when($request->filled('search'), fn ($q) => $q->where(fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%')->orWhere('email', 'like', '%'.$request->string('search').'%')))->when($request->filled('role'), fn ($q) => $q->where('role', $request->string('role')))->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))->latest()->paginate(15)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create(): View
    {
        $this->authorize('create', User::class);

        return view('admin.users.form', ['user' => null, 'institutions' => Institution::orderBy('name')->get()]);
    }

    public function export(Request $request): StreamedResponse
    {
        $this->authorize('viewAny', User::class);
        $users = User::query()->with('institution')->when($request->filled('search'), fn ($q) => $q->where(fn ($query) => $query->where('name', 'like', '%'.$request->string('search').'%')->orWhere('email', 'like', '%'.$request->string('search').'%')))->when($request->filled('role'), fn ($q) => $q->where('role', $request->string('role')))->when($request->filled('status'), fn ($q) => $q->where('status', $request->string('status')))->orderBy('id')->cursor();

        return $this->csv->download('cbt-eduka-users-'.now()->format('Ymd-His').'.csv', ['ID', 'Nama', 'Email', 'Role', 'Status', 'Institusi'], $this->userRows($users));
    }

    public function store(AdminUserRequest $request): RedirectResponse
    {
        $data = $request->validated();
        $data['password'] = Hash::make($data['password']);
        User::create($data);

        return redirect()->route('admin.users.index')->with('status', 'Pengguna berhasil dibuat.');
    }

    public function edit(User $user): View
    {
        $this->authorize('update', $user);

        return view('admin.users.form', ['user' => $user, 'institutions' => Institution::orderBy('name')->get()]);
    }

    public function update(AdminUserRequest $request, User $user): RedirectResponse
    {
        $data = $request->validated();
        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        } else {
            $data['password'] = Hash::make($data['password']);
        } $user->update($data);

        return redirect()->route('admin.users.index')->with('status', 'Pengguna berhasil diperbarui.');
    }

    public function destroy(User $user): RedirectResponse
    {
        $this->authorize('delete', $user);
        $user->update(['status' => 'inactive']);

        return redirect()->route('admin.users.index')->with('status', 'Akun dinonaktifkan.');
    }

    private function userRows(iterable $users): iterable
    {
        foreach ($users as $user) {
            yield [$user->id, $user->name, $user->email, $user->role->value, $user->status->value, $user->institution?->name];
        }
    }
}
