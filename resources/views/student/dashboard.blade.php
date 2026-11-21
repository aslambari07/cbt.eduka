@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-obsidian text-white">
        <div class="mx-auto flex min-h-screen max-w-[1440px]">
            <x-role-sidebar role="student" />

            <main class="min-w-0 flex-1 px-6 py-7 md:px-10 lg:px-12">
                <header class="flex items-center justify-between border-b border-line pb-6">
                    <div>
                        <p class="cbt-eyebrow">RUANG SISWA / OVERVIEW</p>
                        <h1 class="mt-4 font-serif text-4xl font-bold">Selamat pagi, {{ auth()->user()->name }}.</h1>
                        <p class="mt-2 text-sm text-muted">Satu langkah kecil hari ini membawa kamu lebih dekat pada tujuan.</p>
                    </div>
                    <a href="{{ route('student.exams.index') }}" class="cbt-button-primary hidden min-h-11 sm:inline-flex">Ujian saya <iconify-icon icon="lucide:arrow-right"></iconify-icon></a>
                </header>

                @if($nextExam)
                    <section class="mt-7 rounded-2xl border border-line bg-surface p-7">
                        <p class="cbt-eyebrow">BERIKUTNYA UNTUK KAMU</p>
                        <div class="flex flex-col gap-6 md:flex-row md:items-end md:justify-between">
                            <div>
                                <h2 class="mt-4 font-serif text-3xl font-bold">{{ $nextExam->exam->name }}</h2>
                                <p class="mt-3 text-sm text-muted">{{ $nextExam->exam->subject->name }} · {{ $nextExam->exam->duration_minutes }} menit</p>
                                <p class="mt-3 text-xs text-muted">{{ $nextExam->exam->starts_at?->format('d F Y, H:i') ?? 'Jadwal menunggu' }}</p>
                            </div>
                            <a href="{{ route('student.exams.show', $nextExam->exam) }}" class="cbt-button-primary min-h-11">Lihat detail ujian <iconify-icon icon="lucide:arrow-up-right"></iconify-icon></a>
                        </div>
                    </section>
                @endif

                <section class="mt-7 grid grid-cols-2 gap-4 md:grid-cols-4">
                    @foreach([['Ujian selesai', $stats['completed'], 'check-check'], ['Ujian tersedia', $stats['available'], 'layers-3'], ['Sedang berlangsung', $stats['inProgress'], 'radio'], ['Rata-rata nilai', $stats['average'], 'trending-up']] as $stat)
                        <div class="rounded-xl border border-line bg-surface p-5">
                            <div class="flex items-center justify-between"><span class="text-sm text-muted">{{ $stat[0] }}</span><iconify-icon icon="lucide:{{ $stat[2] }}" class="text-orange"></iconify-icon></div>
                            <p class="mt-4 text-3xl font-semibold">{{ is_numeric($stat[1]) && str_contains((string) $stat[1], '.') ? number_format($stat[1], 1) : number_format($stat[1]) }}</p>
                            <p class="mt-2 text-xs text-muted">Data perjalanan belajarmu</p>
                        </div>
                    @endforeach
                </section>

                <section class="mt-9">
                    <div class="flex items-end justify-between"><div><p class="cbt-eyebrow">AKTIVITAS UJIAN</p><h2 class="mt-2 font-serif text-2xl font-bold">Ujian terbaru</h2></div><a href="{{ route('student.exams.index') }}" class="text-xs text-orange">Lihat semua <iconify-icon icon="lucide:arrow-right"></iconify-icon></a></div>
                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        @forelse($assignments as $assignment)
                            <article class="rounded-xl border border-line bg-surface p-5"><span class="rounded-full bg-indigo/10 px-2.5 py-1 text-[10px] text-indigo">{{ $assignment->exam->subject->name }}</span><h3 class="mt-5 text-base font-semibold">{{ $assignment->exam->name }}</h3><p class="mt-2 text-xs text-muted">{{ $assignment->exam->duration_minutes }} menit · {{ ucfirst($assignment->status->value) }}</p><a href="{{ route('student.exams.show', $assignment->exam) }}" class="mt-5 flex min-h-11 w-full items-center justify-center gap-2 rounded-lg border border-line text-xs hover:border-orange hover:text-orange">Mulai / lanjutkan <iconify-icon icon="lucide:arrow-right"></iconify-icon></a></article>
                        @empty
                            <p class="text-sm text-muted">Belum ada ujian yang ditugaskan.</p>
                        @endforelse
                    </div>
                </section>
            </main>
        </div>
    </div>
@endsection
