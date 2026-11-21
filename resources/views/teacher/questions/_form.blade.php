@php
    $questionType = old('type', $question?->type?->value ?? 'multiple_choice');
    $definition = old('definition', $question?->definition ?? []);
    $storedOptions = $question?->options?->map(fn ($option) => [
        'key' => $option->key,
        'label' => $option->label,
        'is_correct' => $option->is_correct,
    ])->all() ?? [];
    $options = old('options', $storedOptions ?: [
        ['key' => 'A', 'label' => '', 'is_correct' => false],
        ['key' => 'B', 'label' => '', 'is_correct' => false],
        ['key' => 'C', 'label' => '', 'is_correct' => false],
        ['key' => 'D', 'label' => '', 'is_correct' => false],
    ]);
    $typeLabels = [
        'multiple_choice' => 'Pilihan ganda',
        'multiple_select' => 'Multiple select',
        'true_false' => 'Benar / salah',
        'short_answer' => 'Jawaban singkat',
        'essay' => 'Esai',
        'matching' => 'Matching',
        'ordering' => 'Ordering',
        'fill_blank' => 'Fill in the blanks',
    ];
    $acceptedAnswers = $definition['accepted_answers'] ?? ['', '', ''];
    $pairs = $definition['pairs'] ?? [];
    if (is_array($pairs) && array_is_list($pairs)) {
        $pairs = collect($pairs)->map(fn ($pair) => ['left' => $pair['left'] ?? '', 'right' => $pair['right'] ?? ''])->all();
    } else {
        $pairs = collect($pairs)->map(fn ($right, $left) => ['left' => $left, 'right' => $right])->values()->all();
    }
    $pairs = $pairs ?: [['left' => '', 'right' => ''], ['left' => '', 'right' => ''], ['left' => '', 'right' => '']];
    $correctOrder = $definition['correct_order'] ?? ['', '', ''];
    $blanks = $definition['blanks'] ?? ['', '', ''];
@endphp

<form action="{{ $formAction }}" method="post" class="space-y-6" data-question-form>
    @csrf
    @if($question)<input type="hidden" name="_method" value="PUT">@endif
    @if($errors->any())
        <div class="rounded-lg border border-error/40 bg-error/10 p-4 text-sm text-error">
            <p class="font-semibold">Periksa kembali data berikut:</p>
            <ul class="mt-2 list-disc space-y-1 pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif
    <div>
        <label for="prompt" class="cbt-label">Teks soal</label>
        <textarea id="prompt" name="prompt" rows="4" class="cbt-input mt-2 h-auto py-3" required>{{ old('prompt', $question?->prompt) }}</textarea>
        @error('prompt')<p class="cbt-error">{{ $message }}</p>@enderror
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        <label class="block"><span class="cbt-label">Mata pelajaran</span><select name="subject_id" class="cbt-input mt-2" required><option value="">Pilih mata pelajaran</option>@foreach($subjects as $subject)<option value="{{ $subject->id }}" @selected((int) old('subject_id', $question?->subject_id) === $subject->id)>{{ $subject->name }}</option>@endforeach</select></label>
        <label class="block"><span class="cbt-label">Tipe soal</span><select id="question-type" name="type" class="cbt-input mt-2" required>@foreach($typeLabels as $value => $label)<option value="{{ $value }}" @selected($questionType === $value)>{{ $label }}</option>@endforeach</select></label>
    </div>
    <div class="grid gap-4 sm:grid-cols-3">
        <label class="block"><span class="cbt-label">Poin</span><input name="points" type="number" step="0.01" min="0.01" max="1000" value="{{ old('points', $question?->points ?? 2) }}" class="cbt-input mt-2" required></label>
        <label class="block"><span class="cbt-label">Kesulitan</span><select name="difficulty" class="cbt-input mt-2"><option value="easy" @selected(old('difficulty', $question?->difficulty ?? 'medium') === 'easy')>Mudah</option><option value="medium" @selected(old('difficulty', $question?->difficulty ?? 'medium') === 'medium')>Sedang</option><option value="hard" @selected(old('difficulty', $question?->difficulty ?? 'medium') === 'hard')>Sulit</option></select></label>
        <label class="block"><span class="cbt-label">Status</span><select name="status" class="cbt-input mt-2"><option value="draft" @selected(old('status', $question?->status ?? 'draft') === 'draft')>Draft</option><option value="published" @selected(old('status', $question?->status ?? 'draft') === 'published')>Terbit</option><option value="archived" @selected(old('status', $question?->status ?? 'draft') === 'archived')>Arsip</option></select></label>
    </div>
    <div>
        <label for="explanation" class="cbt-label">Pembahasan <span class="text-muted">(opsional)</span></label>
        <textarea id="explanation" name="explanation" rows="3" class="cbt-input mt-2 h-auto py-3">{{ old('explanation', $question?->explanation) }}</textarea>
    </div>

    <section id="config-multiple-choice" data-config="multiple_choice,multiple_select" class="rounded-lg border border-line bg-elevated p-4">
        <p class="font-mono text-[10px] tracking-wider text-orange">OPSI JAWABAN</p>
        <p class="mt-1 text-xs text-muted">Pilih satu jawaban benar untuk pilihan ganda, atau beberapa untuk multiple select.</p>
        <div class="mt-4 grid gap-3 sm:grid-cols-2">
            @foreach($options as $index => $option)
                <div class="flex items-center gap-2">
                    <input name="options[{{ $index }}][key]" value="{{ $option['key'] ?? chr(65 + $index) }}" type="hidden" data-config-input>
                    <input name="options[{{ $index }}][label]" value="{{ $option['label'] ?? '' }}" placeholder="Opsi {{ $option['key'] ?? chr(65 + $index) }}" class="cbt-input" data-config-input>
                    <label class="flex shrink-0 items-center gap-1 text-[10px] text-muted"><input name="options[{{ $index }}][is_correct]" value="1" type="checkbox" class="accent-orange" @checked(filter_var($option['is_correct'] ?? false, FILTER_VALIDATE_BOOLEAN)) data-config-input> Benar</label>
                </div>
            @endforeach
        </div>
    </section>

    <section id="config-true-false" data-config="true_false" class="hidden rounded-lg border border-line bg-elevated p-4">
        <p class="font-mono text-[10px] tracking-wider text-orange">JAWABAN BENAR / SALAH</p>
        <div class="mt-4 flex gap-5 text-sm"><label class="flex items-center gap-2"><input type="radio" name="definition[correct]" value="true" @checked(filter_var($definition['correct'] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === true) data-config-input> Benar</label><label class="flex items-center gap-2"><input type="radio" name="definition[correct]" value="false" @checked(filter_var($definition['correct'] ?? null, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) === false && array_key_exists('correct', $definition)) data-config-input> Salah</label></div>
    </section>

    <section id="config-short-answer" data-config="short_answer" class="hidden rounded-lg border border-line bg-elevated p-4">
        <p class="font-mono text-[10px] tracking-wider text-orange">JAWABAN YANG DITERIMA</p>
        <div class="mt-4 grid gap-3 sm:grid-cols-3">@foreach($acceptedAnswers as $index => $answer)<input name="definition[accepted_answers][{{ $index }}]" value="{{ $answer }}" placeholder="Jawaban {{ $index + 1 }}" class="cbt-input" data-config-input>@endforeach</div>
    </section>

    <section id="config-essay" data-config="essay" class="hidden rounded-lg border border-line bg-elevated p-4"><p class="font-mono text-[10px] tracking-wider text-violet">PENILAIAN MANUAL</p><p class="mt-2 text-xs leading-5 text-muted">Jawaban esai akan masuk ke antrean penilaian manual setelah peserta mengirim ujian.</p></section>

    <section id="config-matching" data-config="matching" class="hidden rounded-lg border border-line bg-elevated p-4">
        <p class="font-mono text-[10px] tracking-wider text-orange">PASANGAN MATCHING</p>
        <div class="mt-4 space-y-3">@foreach($pairs as $index => $pair)<div class="grid gap-3 sm:grid-cols-2"><input name="definition[pairs][{{ $index }}][left]" value="{{ $pair['left'] ?? '' }}" placeholder="Item kiri" class="cbt-input" data-config-input><input name="definition[pairs][{{ $index }}][right]" value="{{ $pair['right'] ?? '' }}" placeholder="Pasangan benar" class="cbt-input" data-config-input></div>@endforeach</div>
    </section>

    <section id="config-ordering" data-config="ordering" class="hidden rounded-lg border border-line bg-elevated p-4">
        <p class="font-mono text-[10px] tracking-wider text-orange">URUTAN BENAR</p>
        <div class="mt-4 grid gap-3 sm:grid-cols-3">@foreach($correctOrder as $index => $item)<input name="definition[correct_order][{{ $index }}]" value="{{ $item }}" placeholder="Posisi {{ $index + 1 }}" class="cbt-input" data-config-input>@endforeach</div>
    </section>

    <section id="config-fill-blank" data-config="fill_blank" class="hidden rounded-lg border border-line bg-elevated p-4">
        <p class="font-mono text-[10px] tracking-wider text-orange">JAWABAN ISIAN</p>
        <div class="mt-4 grid gap-3 sm:grid-cols-3">@foreach($blanks as $index => $blank)<input name="definition[blanks][{{ $index }}]" value="{{ is_array($blank) ? implode(', ', $blank) : $blank }}" placeholder="Jawaban isian {{ $index + 1 }}" class="cbt-input" data-config-input>@endforeach</div>
        <p class="mt-2 text-[10px] text-muted">Pisahkan alternatif jawaban dengan koma.</p>
    </section>

    <div class="flex items-center justify-end gap-3 border-t border-line pt-6"><a href="{{ route('teacher.questions.index') }}" class="cbt-button-secondary">Batal</a><button class="cbt-button-primary">Simpan pertanyaan <iconify-icon icon="lucide:save"></iconify-icon></button></div>
</form>

<script>
(() => {
    const select = document.getElementById('question-type');
    const sections = [...document.querySelectorAll('[data-config]')];
    const update = () => sections.forEach((section) => {
        const visible = section.dataset.config.split(',').includes(select.value);
        section.classList.toggle('hidden', !visible);
        section.querySelectorAll('[data-config-input]').forEach((input) => { input.disabled = !visible; });
    });
    select.addEventListener('change', update);
    update();
})();
</script>
