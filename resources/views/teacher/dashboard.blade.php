@extends('layouts.app')

@section('content')
    <div class="min-h-screen bg-obsidian text-white">
        <div class="mx-auto flex min-h-screen max-w-[1440px]">
            <x-role-sidebar role="teacher" />

            <main class="min-w-0 flex-1 px-6 py-7 md:px-10 lg:px-12">
                <header class="flex flex-col gap-5 border-b border-line pb-6 sm:flex-row sm:items-end sm:justify-between">
                    <div>
                        <p class="cbt-eyebrow">RUANG GURU / RINGKASAN</p>
                        <h1 class="mt-4 font-serif text-4xl font-bold">Selamat pagi, {{ auth()->user()->name }}.</h1>
                        <p class="mt-2 text-sm text-muted">Kelola ujian, pantau kemajuan, dan bantu siswa belajar lebih baik.</p>
                    </div>
                    <a href="{{ route('teacher.exams.create') }}" class="cbt-button-primary min-h-12">Buat ujian baru <iconify-icon icon="lucide:plus"></iconify-icon></a>
                </header>

                <section class="mt-8 grid grid-cols-2 gap-4 xl:grid-cols-5">
                    @foreach([['Kelas', $stats['classes'], 'users-round'], ['Peserta', $stats['students'], 'users'], ['Total ujian', $stats['exams'], 'files'], ['Ujian aktif', $stats['activeExams'], 'radio'], ['Bank soal', $stats['questions'], 'library-big']] as $stat)
                        <div class="rounded-xl border border-line bg-surface p-5">
                            <div class="flex items-center justify-between"><span class="text-sm text-muted">{{ $stat[0] }}</span><iconify-icon icon="lucide:{{ $stat[2] }}" class="text-orange"></iconify-icon></div>
                            <p class="mt-4 text-3xl font-semibold">{{ number_format($stat[1]) }}</p>
                            <p class="mt-2 text-xs text-muted">Terhubung ke ruang kerja</p>
                        </div>
                    @endforeach
                </section>

                <section class="mt-8 rounded-xl border border-line bg-surface p-6">
                    <div class="flex items-end justify-between">
                        <div><p class="cbt-eyebrow">AKTIVITAS TERBARU</p><h2 class="mt-2 text-lg font-semibold">Ujian yang perlu diperhatikan</h2></div>
                        <a href="{{ route('teacher.exams.index') }}" class="text-xs text-orange">Lihat semua <iconify-icon icon="lucide:arrow-right"></iconify-icon></a>
                    </div>
                    <div class="mt-5 divide-y divide-line">
                        @forelse($exams as $exam)
                            <div class="flex flex-col gap-3 py-4 sm:flex-row sm:items-center sm:justify-between">
                                <div><p class="text-sm font-medium">{{ $exam->name }}</p><p class="mt-1 font-mono text-[10px] text-muted">{{ $exam->code }} · {{ $exam->subject->name }}</p></div>
                                <div class="flex items-center gap-4"><span class="rounded-full bg-orange/10 px-2.5 py-1 text-[10px] text-orange">{{ ucfirst($exam->status->value) }}</span><a class="text-xs text-orange" href="{{ route('teacher.exams.edit', $exam) }}">Kelola</a></div>
                            </div>
                        @empty
                            <p class="py-8 text-sm text-muted">Belum ada ujian.</p>
                        @endforelse
                    </div>
                </section>
            </main>
        </div>
    </div>
@endsection
