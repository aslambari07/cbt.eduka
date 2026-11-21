@extends('layouts.app')

@section('content')
@php
    $statusLabel = match ($aiStatus->value) {
        'READY' => 'AI siap membantu',
        'DISABLED' => 'AI dinonaktifkan',
        'UNAVAILABLE' => 'Layanan AI tidak tersedia',
        'RATE_LIMITED' => 'Batas AI tercapai',
        'AUTHENTICATION_FAILED' => 'API key AI tidak valid',
        'PROVIDER_ERROR' => 'Provider AI bermasalah',
        default => 'Provider AI belum dikonfigurasi',
    };
    $statusClass = $aiReady
        ? 'border-success/30 bg-success/5 text-success'
        : 'border-warning/30 bg-warning/5 text-warning';
    $drafts = collect($generatedQuestions);
    $defaultSubjectId = old('subject_id', data_get($generationParameters, 'subject_id'));
@endphp

<div class="min-h-screen bg-obsidian text-white">
    <div class="mx-auto max-w-[1280px] px-6 py-8 sm:px-10">
        <header class="flex flex-col gap-5 border-b border-line pb-6 lg:flex-row lg:items-end lg:justify-between">
            <div>
                <p class="cbt-eyebrow text-violet">EDUKA INTELLIGENCE / QUESTION STUDIO</p>
                <h1 class="mt-3 font-serif text-4xl font-bold tracking-tight">Buat soal dengan AI.</h1>
                <p class="mt-2 max-w-2xl text-sm text-muted">Gunakan Groq untuk membuat draft soal, tinjau seluruh isi, lalu simpan hanya soal yang Anda setujui ke bank soal.</p>
            </div>
            <div class="flex items-center gap-2 rounded-lg border px-3 py-2 text-xs {{ $statusClass }}" data-ai-status="{{ $aiStatus->value }}">
                <span class="h-2 w-2 rounded-full bg-current"></span>
                <span>{{ $statusLabel }}</span>
                @if ($aiStatus === \App\Enums\AiProviderStatus::READY)
                    <span class="font-mono text-[10px] opacity-80">{{ config('cbt.ai.model') }}</span>
                @endif
            </div>
        </header>

        @if (session('ai_status'))
            <div class="mt-5 rounded-lg border {{ session('ai_status_code') === 'READY' || session('ai_status_code') === 'SAVED' ? 'border-success/30 bg-success/5 text-success' : 'border-warning/30 bg-warning/5 text-warning' }} px-4 py-3 text-sm" role="status">
                {{ session('ai_status') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="mt-5 rounded-lg border border-error/30 bg-error/5 px-4 py-3 text-sm text-error" role="alert">
                <p class="font-medium">Periksa kembali data yang dikirim.</p>
                <ul class="mt-2 list-disc space-y-1 pl-5 text-xs">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="mt-8 grid gap-5 xl:grid-cols-[420px_minmax(0,1fr)]">
            <section class="rounded-xl border border-line bg-surface p-6">
                <p class="cbt-eyebrow">01 / PARAMETER</p>
                <h2 class="mt-2 text-lg font-semibold">Atur pertanyaan</h2>
                <form method="post" action="{{ route('teacher.ai.generate') }}" class="mt-6 space-y-4">
                    @csrf
                    <label class="block">
                        <span class="cbt-label">Mata pelajaran</span>
                        <select class="cbt-input mt-2" name="subject_id">
                            <option value="">Pilih untuk mengaktifkan penyimpanan draft</option>
                            @foreach ($subjects as $subject)
                                <option value="{{ $subject->id }}" @selected((string) $defaultSubjectId === (string) $subject->id)>{{ $subject->name }} ({{ $subject->code }})</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="block">
                        <span class="cbt-label">Nama mata pelajaran / konteks</span>
                        <input class="cbt-input mt-2" name="subject" value="{{ old('subject', data_get($generationParameters, 'subject', 'Matematika')) }}" required maxlength="160">
                    </label>
                    <label class="block">
                        <span class="cbt-label">Topik atau materi</span>
                        <input class="cbt-input mt-2" name="topic" value="{{ old('topic', data_get($generationParameters, 'topic', 'Limit dan turunan')) }}" required maxlength="300">
                    </label>
                    <label class="block">
                        <span class="cbt-label">Deskripsi materi <span class="text-muted">(opsional)</span></span>
                        <textarea class="cbt-input mt-2 min-h-24" name="description" maxlength="5000" placeholder="Konsep, tujuan belajar, atau batasan materi">{{ old('description', data_get($generationParameters, 'description')) }}</textarea>
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="block">
                            <span class="cbt-label">Kesulitan</span>
                            <select class="cbt-input mt-2" name="difficulty">
                                @foreach (['easy' => 'Mudah', 'medium' => 'Sedang', 'hard' => 'Sulit'] as $value => $label)
                                    <option value="{{ $value }}" @selected(old('difficulty', data_get($generationParameters, 'difficulty', 'medium')) === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </label>
                        <label class="block">
                            <span class="cbt-label">Jumlah soal</span>
                            <input class="cbt-input mt-2" type="number" name="count" value="{{ old('count', data_get($generationParameters, 'count', 5)) }}" min="1" max="{{ config('cbt.ai.max_questions', 20) }}" required>
                        </label>
                    </div>
                    <label class="block">
                        <span class="cbt-label">Tipe pertanyaan</span>
                        <select class="cbt-input mt-2" name="type">
                            @foreach (['multiple_choice' => 'Pilihan ganda', 'multiple_select' => 'Multiple select', 'true_false' => 'Benar / salah', 'short_answer' => 'Jawaban singkat', 'essay' => 'Esai', 'matching' => 'Matching', 'ordering' => 'Ordering', 'fill_blank' => 'Fill in the blanks'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('type', data_get($generationParameters, 'type', 'multiple_choice')) === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </label>
                    <div class="grid grid-cols-2 gap-3">
                        <label class="block">
                            <span class="cbt-label">Bahasa</span>
                            <select class="cbt-input mt-2" name="language">
                                <option @selected(old('language', data_get($generationParameters, 'language', 'Bahasa Indonesia')) === 'Bahasa Indonesia')>Bahasa Indonesia</option>
                                <option @selected(old('language', data_get($generationParameters, 'language')) === 'English')>English</option>
                            </select>
                        </label>
                        <label class="block">
                            <span class="cbt-label">Tingkat kelas</span>
                            <select class="cbt-input mt-2" name="grade">
                                @foreach (['XII IPA', 'XI IPA', 'X IPS'] as $grade)
                                    <option @selected(old('grade', data_get($generationParameters, 'grade', 'XII IPA')) === $grade)>{{ $grade }}</option>
                                @endforeach
                            </select>
                        </label>
                    </div>
                    <label class="block">
                        <span class="cbt-label">Instruksi tambahan <span class="text-muted">(opsional)</span></span>
                        <textarea class="cbt-input mt-2 min-h-20" name="instructions" maxlength="1500" placeholder="Gaya soal, konteks, atau batasan khusus">{{ old('instructions', data_get($generationParameters, 'instructions')) }}</textarea>
                    </label>
                    <button class="cbt-button-primary min-h-12 w-full justify-center" type="submit" @disabled(! $aiReady)>
                        <iconify-icon icon="lucide:sparkles"></iconify-icon>
                        Generate dengan AI
                    </button>
                    @if (! $aiReady)
                        <p class="text-[11px] leading-5 text-muted">Generation dinonaktifkan sampai status provider READY. Tidak ada soal sintetis yang digunakan sebagai fallback.</p>
                    @endif
                </form>
            </section>

            <section class="rounded-xl border border-line bg-surface p-6">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="cbt-eyebrow">02 / HASIL GENERASI</p>
                        <h2 class="mt-2 text-lg font-semibold">Pertanyaan yang dihasilkan</h2>
                        <p class="mt-1 text-xs text-muted">Draft tidak disimpan otomatis. Edit, pilih, lalu simpan secara eksplisit.</p>
                    </div>
                    @if ($drafts->isNotEmpty())
                        <span class="font-mono text-[10px] text-muted">{{ $drafts->count() }} DRAFT</span>
                    @endif
                </div>

                @if ($drafts->isNotEmpty())
                    <form method="post" action="{{ route('teacher.ai.store') }}" class="mt-5" data-ai-save-form>
                        @csrf
                        <textarea name="questions" class="hidden" data-ai-payload aria-hidden="true"></textarea>
                        <div class="space-y-4">
                            @foreach ($drafts as $index => $question)
                                <article class="rounded-lg border border-line bg-elevated p-4" data-ai-draft>
                                    <div class="flex items-start justify-between gap-4">
                                        <label class="flex items-center gap-2 text-xs font-medium">
                                            <input type="checkbox" class="h-4 w-4 accent-orange" data-ai-selected checked>
                                            <span class="font-mono text-[10px] text-orange">DRAFT {{ str_pad((string) ($index + 1), 2, '0', STR_PAD_LEFT) }}</span>
                                        </label>
                                        <button type="button" class="text-xs text-muted transition hover:text-error" data-ai-remove><iconify-icon icon="lucide:trash-2"></iconify-icon> Hapus</button>
                                    </div>
                                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                                        <label class="block sm:col-span-2"><span class="cbt-label">Mata pelajaran penyimpanan</span><select class="cbt-input mt-2" data-ai-subject-id>
                                            <option value="">Pilih mata pelajaran</option>
                                            @foreach ($subjects as $subject)
                                                <option value="{{ $subject->id }}" @selected((string) ($question['subject_id'] ?? $defaultSubjectId) === (string) $subject->id)>{{ $subject->name }} ({{ $subject->code }})</option>
                                            @endforeach
                                        </select></label>
                                        <label class="block sm:col-span-2"><span class="cbt-label">Pertanyaan</span><textarea class="cbt-input mt-2 min-h-24" data-ai-prompt required>{{ $question['prompt'] ?? '' }}</textarea></label>
                                        <label class="block"><span class="cbt-label">Tipe</span><select class="cbt-input mt-2" data-ai-type>
                                            @foreach (['multiple_choice' => 'Pilihan ganda', 'multiple_select' => 'Multiple select', 'true_false' => 'Benar / salah', 'short_answer' => 'Jawaban singkat', 'essay' => 'Esai', 'matching' => 'Matching', 'ordering' => 'Ordering', 'fill_blank' => 'Fill in the blanks'] as $value => $label)
                                                <option value="{{ $value }}" @selected(($question['type'] ?? '') === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select></label>
                                        <label class="block"><span class="cbt-label">Poin</span><input class="cbt-input mt-2" type="number" min="0.01" max="1000" step="0.01" value="{{ $question['points'] ?? 1 }}" data-ai-points required></label>
                                        <label class="block sm:col-span-2"><span class="cbt-label">Pilihan jawaban <span class="text-muted">(key|label|benar)</span></span>
                                            <div class="mt-2 space-y-2" data-ai-options>
                                                @foreach ($question['options'] ?? [] as $option)
                                                    <div class="grid grid-cols-[72px_minmax(0,1fr)_auto] items-center gap-2" data-ai-option><input class="cbt-input" value="{{ $option['key'] ?? '' }}" data-ai-option-key maxlength="10"><input class="cbt-input" value="{{ $option['label'] ?? '' }}" data-ai-option-label><label class="flex items-center gap-1 text-[10px] text-muted"><input type="checkbox" class="h-4 w-4 accent-orange" data-ai-option-correct @checked($option['is_correct'] ?? false)> Benar</label></div>
                                                @endforeach
                                            </div>
                                        </label>
                                        <label class="block sm:col-span-2"><span class="cbt-label">Definition JSON untuk tipe non-pilihan</span><textarea class="cbt-input mt-2 min-h-28 font-mono text-[11px]" data-ai-definition required>{{ json_encode($question['definition'] ?? [], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</textarea><span class="mt-1 block text-[10px] text-muted">Contoh: accepted_answers, correct, pairs, correct_order, blanks, atau rubric.</span></label>
                                        <label class="block sm:col-span-2"><span class="cbt-label">Penjelasan</span><textarea class="cbt-input mt-2 min-h-20" data-ai-explanation required>{{ $question['explanation'] ?? '' }}</textarea></label>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                        <div class="mt-5 flex flex-col gap-3 border-t border-line pt-5 sm:flex-row sm:items-center sm:justify-between">
                            <p class="text-[11px] leading-5 text-muted">Soal disimpan sebagai draft milik Anda. Soal tidak otomatis masuk ke ujian yang telah dipublikasikan.</p>
                            <button class="cbt-button-primary min-h-11 justify-center" type="submit"><iconify-icon icon="lucide:save"></iconify-icon>Simpan yang dipilih</button>
                        </div>
                    </form>
                @else
                    <div class="mt-6 rounded-lg border border-dashed border-line bg-elevated p-10 text-center">
                        <iconify-icon icon="lucide:sparkles" class="text-3xl text-violet"></iconify-icon>
                        <p class="mt-4 text-sm font-medium">Belum ada draft soal.</p>
                        <p class="mt-2 text-xs leading-5 text-muted">Hasil provider AI akan tampil sebagai draft dan wajib ditinjau pengajar sebelum disimpan.</p>
                    </div>
                @endif
            </section>
        </div>
    </div>
</div>
@endsection
