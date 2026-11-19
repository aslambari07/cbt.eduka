@extends('layouts.app')

@section('content')
<div class="cbt-atmosphere min-h-screen overflow-hidden">
    <div class="cbt-grid pointer-events-none fixed inset-0"></div>
    <header class="relative z-10 mx-auto flex max-w-7xl items-center justify-between px-6 py-6 sm:px-10">
        <a href="{{ url('/') }}" class="cbt-brand" aria-label="CBT.EDUKA beranda"><span class="cbt-mark">C</span><span>CBT<span class="text-orange">.EDUKA</span></span></a>
        <nav class="hidden items-center gap-8 text-sm text-muted md:flex" aria-label="Navigasi utama">
            <a href="#platform" class="cbt-link">Platform</a>
            <a href="#security" class="cbt-link">Keamanan</a>
            <a href="#workflow" class="cbt-link">Cara kerja</a>
        </nav>
        <div class="flex items-center gap-3">
            <a href="{{ route('login') }}" class="hidden min-h-11 items-center px-3 text-sm text-muted transition hover:text-white sm:inline-flex">Masuk</a>
            <a href="{{ route('register') }}" class="cbt-button-primary min-h-11">Mulai gratis <iconify-icon icon="lucide:arrow-up-right"></iconify-icon></a>
        </div>
    </header>

    <main id="platform" class="relative z-10 mx-auto max-w-7xl px-6 sm:px-10">
        <section class="grid min-h-[calc(100vh-88px)] items-center gap-12 pb-16 pt-10 lg:grid-cols-12 lg:gap-8">
            <div class="lg:col-span-7">
                <p class="cbt-eyebrow"><span></span> CBT UNTUK ERA BARU</p>
                <h1 class="mt-6 max-w-3xl font-serif text-5xl font-bold leading-[1.03] tracking-[-.045em] sm:text-6xl">Ujian online yang<br><span class="text-orange">jujur, cepat,</span><br>dan terukur.</h1>
                <p class="mt-7 max-w-xl text-base leading-8 text-muted sm:text-lg">Bangun pengalaman ujian digital yang lebih sederhana untuk pengajar, lebih aman untuk institusi, dan lebih fokus untuk setiap siswa.</p>
                <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                    <a href="{{ route('register') }}" class="cbt-button-primary min-h-12 justify-center px-5">Coba CBT.EDUKA <iconify-icon icon="lucide:arrow-right"></iconify-icon></a>
                    <a href="#workflow" class="cbt-button-secondary min-h-12 justify-center px-5">Lihat cara kerja <iconify-icon icon="lucide:play-circle" class="text-orange"></iconify-icon></a>
                </div>
                <div class="mt-12 flex flex-wrap gap-x-7 gap-y-3 font-mono text-[10px] tracking-wider text-muted"><span class="cbt-proof"><i class="bg-orange"></i> SETUP DALAM MENIT</span><span class="cbt-proof"><i class="bg-white"></i> DATA TERJAGA</span><span class="cbt-proof"><i class="bg-white"></i> SIAP MOBILE</span></div>
            </div>

            <div class="relative min-h-[470px] lg:col-span-5">
                <div class="cbt-orbit cbt-orbit-one"></div><div class="cbt-orbit cbt-orbit-two"></div>
                <div class="cbt-sphere"></div>
                <div class="cbt-exam-preview">
                    <div class="flex items-center justify-between border-b border-line pb-4"><div class="cbt-brand text-xs"><span class="cbt-mark h-7 w-7 text-[10px]">C</span><span>CBT<span class="text-orange">.EDUKA</span></span></div><span class="font-mono text-[10px] text-orange">01 / 20</span></div>
                    <p class="mt-5 font-mono text-[9px] text-muted">SOAL PILIHAN GANDA</p><h2 class="mt-2 font-serif text-lg font-bold leading-snug">Apa tujuan utama evaluasi pembelajaran?</h2>
                    <div class="mt-4 space-y-2"><div class="flex items-center gap-2 rounded border border-orange/60 bg-orange/10 px-3 py-2 text-[11px]"><b class="flex h-5 w-5 items-center justify-center rounded-full bg-orange text-[9px]">A</b> Mengukur pencapaian kompetensi</div><div class="flex items-center gap-2 rounded border border-line px-3 py-2 text-[11px] text-muted"><b class="flex h-5 w-5 items-center justify-center rounded-full border border-line text-[9px]">B</b> Menambah beban belajar</div></div>
                    <div class="mt-5 flex items-center justify-between border-t border-line pt-4"><span class="font-mono text-[9px] text-success">● TERSIMPAN</span><span class="font-mono text-[10px] text-orange">42:18</span></div>
                </div>
                <div class="cbt-live-card"><div class="flex items-center justify-between font-mono text-[9px] text-muted">LIVE MONITORING <i class="h-2 w-2 rounded-full bg-success shadow-[0_0_10px_#10B981]"></i></div><p class="mt-4 text-3xl font-semibold">1,248</p><p class="mt-1 text-[10px] text-muted">peserta aktif hari ini</p><div class="mt-4 flex h-8 items-end gap-1"><i class="h-3 flex-1 rounded-sm bg-orange/40"></i><i class="h-5 flex-1 rounded-sm bg-orange/60"></i><i class="h-4 flex-1 rounded-sm bg-orange/50"></i><i class="h-7 flex-1 rounded-sm bg-orange"></i><i class="h-6 flex-1 rounded-sm bg-orange/70"></i><i class="h-8 flex-1 rounded-sm bg-orange"></i></div></div>
                <p class="absolute bottom-0 left-0 font-mono text-[10px] tracking-[.2em] text-muted">PRECISION / CLARITY / TRUST</p>
            </div>
        </section>
        <section id="workflow" class="border-t border-line py-10"><div class="grid gap-8 md:grid-cols-3"><div><p class="cbt-eyebrow">01 / EXPERIENCE</p><h2 class="mt-3 font-serif text-2xl font-bold">Satu ruang untuk belajar.</h2></div><p class="text-sm leading-7 text-muted">Guru menyusun ujian, siswa mengerjakan tanpa distraksi, dan setiap jawaban tersimpan dengan jelas.</p><p id="security" class="text-sm leading-7 text-muted">Status, hasil, dan progres dibangun dari data nyata—bukan angka pajangan.</p></div></section>
    </main>
</div>
@endsection
