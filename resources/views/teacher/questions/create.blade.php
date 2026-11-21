@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-obsidian px-6 py-8 text-white sm:px-10">
    <div class="mx-auto max-w-4xl">
        <a href="{{ route('teacher.questions.index') }}" class="text-xs text-muted hover:text-white"><iconify-icon icon="lucide:arrow-left"></iconify-icon> Kembali ke bank soal</a>
        <div class="mt-7"><p class="cbt-eyebrow">BANK SOAL / SOAL BARU</p><h1 class="mt-3 font-serif text-4xl font-bold">Buat pertanyaan</h1></div>
        <div class="cbt-card mt-7">
            @include('teacher.questions._form', ['formAction' => route('teacher.questions.store'), 'question' => null])
        </div>
    </div>
</div>
@endsection
