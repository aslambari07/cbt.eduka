@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-obsidian text-white"><div class="mx-auto flex min-h-screen max-w-3xl items-center px-6 py-10"><article class="w-full rounded-xl border border-line bg-surface p-8 text-center"><p class="cbt-eyebrow justify-center">VERIFIKASI SERTIFIKAT</p>@if($certificate)<iconify-icon icon="lucide:badge-check" class="mt-7 text-5xl text-success"></iconify-icon><h1 class="mt-5 font-serif text-3xl font-bold">Sertifikat valid</h1><p class="mt-3 text-sm text-muted">{{ $certificate->student->name }} · {{ $certificate->result->exam->name }}</p><p class="mt-4 font-mono text-orange">{{ $certificate->certificate_number }}</p>@else<iconify-icon icon="lucide:badge-alert" class="mt-7 text-5xl text-error"></iconify-icon><h1 class="mt-5 font-serif text-3xl font-bold">Sertifikat tidak ditemukan</h1><p class="mt-3 text-sm text-muted">Nomor {{ $number }} tidak terdaftar atau sudah dicabut.</p>@endif</article></div></div>
@endsection
