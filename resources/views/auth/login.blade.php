@extends('layouts.auth')

@section('content')
<div class="cbt-atmosphere relative min-h-screen overflow-hidden">
    <div class="cbt-grid pointer-events-none fixed inset-0"></div>
    <main class="relative z-10 mx-auto grid min-h-screen max-w-7xl lg:grid-cols-2">
        <section class="hidden flex-col justify-between border-r border-line px-10 py-10 lg:flex xl:px-16">
            <a href="{{ url('/') }}" class="cbt-brand"><span class="cbt-mark">C</span><span>CBT<span class="text-orange">.EDUKA</span></span></a>
            <div class="max-w-xl"><p class="cbt-eyebrow"><span></span> SELAMAT DATANG KEMBALI</p><h1 class="mt-7 font-serif text-5xl font-bold leading-[1.04] tracking-[-.045em]">Ujian online yang<br><span class="text-orange">jujur, cepat,</span><br>dan terukur.</h1><p class="mt-7 max-w-md text-base leading-8 text-muted">Satu ruang terintegrasi untuk belajar, menguji kemampuan, dan melihat perkembanganmu dengan lebih jelas.</p><div class="mt-12 grid grid-cols-3 gap-5 border-t border-line pt-5 text-[11px] text-muted"><div><span class="flex items-center gap-2 text-xs font-medium text-white"><iconify-icon icon="lucide:shield-check" class="text-orange"></iconify-icon>Aman</span><p class="mt-2 leading-5">Data terlindungi.</p></div><div><span class="flex items-center gap-2 text-xs font-medium text-white"><iconify-icon icon="lucide:zap" class="text-orange"></iconify-icon>Cepat</span><p class="mt-2 leading-5">Mulai tanpa hambatan.</p></div><div><span class="flex items-center gap-2 text-xs font-medium text-white"><iconify-icon icon="lucide:smartphone" class="text-orange"></iconify-icon>Mobile-ready</span><p class="mt-2 leading-5">Di perangkat apa pun.</p></div></div></div>
            <footer class="flex items-center justify-between font-mono text-[10px] text-muted"><span>CBT.EDUKA / 2024</span><span class="text-success">● SEMUA SISTEM AKTIF</span></footer>
        </section>
        <section class="flex items-center justify-center px-6 py-12 sm:px-10 xl:px-16"><div class="w-full max-w-md"><div class="mb-9"><p class="cbt-eyebrow">AKSES AKUN</p><h2 class="mt-3 text-3xl font-semibold tracking-tight">Masuk ke CBT.EDUKA</h2><p class="mt-2 text-sm text-muted">Lanjutkan perjalanan belajarmu.</p></div>
            <form class="space-y-5" action="{{ route('login.store') }}" method="post">@csrf
                <div><label for="email" class="cbt-label">Alamat email</label><div class="relative mt-2"><iconify-icon icon="lucide:mail" class="cbt-input-icon"></iconify-icon><input id="email" name="email" type="email" value="{{ old('email') }}" autocomplete="email" class="cbt-input @error('email') border-error @enderror" required autofocus></div>@error('email')<p class="cbt-error">{{ $message }}</p>@enderror</div>
                <div><div class="flex items-center justify-between"><label for="password" class="cbt-label">Kata sandi</label><a href="{{ route('password.request') }}" class="text-[11px] text-orange hover:text-white">Lupa kata sandi?</a></div><div class="relative mt-2"><iconify-icon icon="lucide:lock-keyhole" class="cbt-input-icon"></iconify-icon><input id="password" name="password" type="password" autocomplete="current-password" class="cbt-input pr-11 @error('password') border-error @enderror" required><button type="button" data-password-toggle="password" class="absolute right-3 top-1/2 -translate-y-1/2 text-muted hover:text-white" aria-label="Tampilkan kata sandi"><iconify-icon icon="lucide:eye"></iconify-icon></button></div>@error('password')<p class="cbt-error">{{ $message }}</p>@enderror</div>
                <label class="flex items-center gap-2 pt-1 text-xs text-muted"><input name="remember" type="checkbox" value="1" class="h-4 w-4 accent-orange"> Ingat saya di perangkat ini</label>
                <button type="submit" class="cbt-button-primary mt-2 h-12 w-full justify-center">Masuk ke akun <iconify-icon icon="lucide:arrow-right"></iconify-icon></button>
            </form>
            <div class="my-7 flex items-center gap-4"><div class="h-px flex-1 bg-line"></div><span class="font-mono text-[10px] text-muted">ATAU</span><div class="h-px flex-1 bg-line"></div></div>
            <a href="{{ route('register') }}" class="cbt-button-secondary h-12 w-full justify-center">Buat akun baru <iconify-icon icon="lucide:arrow-up-right" class="text-orange"></iconify-icon></a>
            <p class="mt-8 text-center text-[10px] leading-5 text-muted">Dengan masuk, kamu menyetujui <a href="#terms" class="text-white underline underline-offset-2">Syarat Penggunaan</a> dan <a href="#privacy" class="text-white underline underline-offset-2">Kebijakan Privasi</a>.</p>
        </div></section>
    </main>
</div>
@endsection
