@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-obsidian text-white"><div class="mx-auto max-w-6xl px-6 py-8 sm:px-10"><header class="border-b border-line pb-6"><a href="{{ route('teacher.exams.index') }}" class="text-xs text-muted hover:text-white"><iconify-icon icon="lucide:arrow-left" class="align-middle"></iconify-icon> Kembali ke ujian</a><p class="cbt-eyebrow mt-6">EXAM BUILDER / LANGKAH 1–7</p><h1 class="mt-3 font-serif text-4xl font-bold">Buat ujian baru</h1><p class="mt-2 text-sm text-muted">Simpan sebagai draft, pilih soal, lalu publikasikan setelah konfigurasi siap.</p></header><div class="mt-7 rounded-xl border border-line bg-surface p-6">@include('teacher.exams._form', ['exam' => null, 'formAction' => route('teacher.exams.store')])</div></div></div>
@endsection
