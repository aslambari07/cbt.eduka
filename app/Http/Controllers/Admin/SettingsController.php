<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SystemSettingsRequest;
use App\Models\SystemSetting;
use App\Services\AuditLogService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class SettingsController extends Controller
{
    public function __construct(private readonly AuditLogService $auditLog) {}

    public function edit(): View
    {
        $this->authorizeAdmin();

        $settings = SystemSetting::query()->get()->keyBy('setting_key')->map(fn (SystemSetting $setting) => $setting->value['value'] ?? null);

        return view('admin.settings.edit', compact('settings'));
    }

    public function update(SystemSettingsRequest $request): RedirectResponse
    {
        $this->authorizeAdmin();
        $data = $request->validated();
        $data['maintenance_mode'] = $request->boolean('maintenance_mode');

        foreach ($data as $key => $value) {
            SystemSetting::updateOrCreate(
                ['setting_key' => $key],
                ['value' => ['value' => $value], 'is_sensitive' => false],
            );
        }

        $this->auditLog->record($request->user(), 'system_settings.updated', null, ['keys' => array_keys($data)], $request);

        return back()->with('status', 'Pengaturan platform berhasil disimpan.');
    }

    private function authorizeAdmin(): void
    {
        abort_unless(request()->user()?->hasRole('admin') && request()->user()->isActive(), 403);
    }
}
