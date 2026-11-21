@extends('layouts.app')

@section('content')
<div class="min-h-screen bg-obsidian text-white">
    <div class="mx-auto flex min-h-screen max-w-[1440px]">
        @include('admin._nav')
        <main class="min-w-0 flex-1 px-6 py-7 md:px-10 lg:px-12">
            <header class="border-b border-line pb-6">
                <p class="cbt-eyebrow">ADMIN CONSOLE / OVERVIEW</p>
                <h1 class="mt-4 font-serif text-4xl font-bold">Selamat datang, {{ auth()->user()->name }}.</h1>
                <p class="mt-2 text-sm text-muted">Pantau kesehatan platform dan seluruh ekosistem CBT.EDUKA.</p>
            </header>

            <section class="mt-7 grid grid-cols-2 gap-4 xl:grid-cols-4">
                @foreach([['Total pengguna', $stats['users'], 'users'], ['Institusi', $stats['institutions'], 'building-2'], ['Pengajar', $stats['teachers'], 'graduation-cap'], ['Peserta', $stats['students'], 'users-round'], ['Kelas', $stats['classes'], 'school'], ['Mata pelajaran', $stats['subjects'], 'book-open'], ['Total ujian', $stats['exams'], 'files'], ['Ujian aktif', $stats['activeExams'], 'radio']] as $stat)
                    <div class="rounded-xl border border-line bg-surface p-5">
                        <div class="flex items-center justify-between"><span class="text-sm text-muted">{{ $stat[0] }}</span><span class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange/10 text-orange"><iconify-icon icon="lucide:{{ $stat[2] }}"></iconify-icon></span></div>
                        <p class="mt-4 text-3xl font-semibold">{{ number_format($stat[1]) }}</p>
                        <p class="mt-2 text-xs text-muted">Data aktual platform</p>
                    </div>
                @endforeach
            </section>

            <section class="mt-8 rounded-xl border border-line bg-surface p-6">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-end sm:justify-between">
                    <div><p class="cbt-eyebrow">AKTIVITAS TERBARU</p><h2 class="mt-2 text-lg font-semibold">Ujian terbaru</h2></div>
                    <a href="{{ route('teacher.exams.index') }}" class="text-xs text-orange hover:text-white">Kelola semua ujian <iconify-icon icon="lucide:arrow-up-right"></iconify-icon></a>
                </div>
                <div class="mt-5 divide-y divide-line">
                    @forelse($recentExams as $exam)
                        <div class="flex flex-col gap-2 py-4 sm:flex-row sm:items-center sm:justify-between">
                            <div><p class="text-sm font-medium">{{ $exam->name }}</p><p class="mt-1 text-xs text-muted">{{ $exam->subject->name }} · {{ $exam->owner->name }}</p></div>
                            <span class="w-fit rounded-full bg-orange/10 px-2.5 py-1 text-[10px] text-orange">{{ ucfirst($exam->status->value) }}</span>
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
