@extends('layouts.auth')

@section('content')
<div class="flex min-h-screen items-center justify-center px-6"><div class="w-full max-w-md"><a href="{{ route('login') }}" class="cbt-brand"><span class="cbt-mark">C</span><span>CBT<span class="text-orange">.EDUKA</span></span></a><div class="cbt-card mt-10"><p class="cbt-eyebrow">PEMULIHAN AKUN</p><h1 class="mt-3 text-3xl font-semibold">Lupa kata sandi?</h1><p class="mt-2 text-sm leading-6 text-muted">Masukkan email akunmu. Pengiriman link pemulihan akan diaktifkan bersama konfigurasi mail aplikasi.</p><form action="#" method="post" class="mt-6 space-y-4">@csrf<label for="recovery-email" class="cbt-label">Alamat email</label><input id="recovery-email" name="email" type="email" class="cbt-input" required><button class="cbt-button-primary h-12 w-full justify-center" type="submit">Kirim link pemulihan <iconify-icon icon="lucide:arrow-right"></iconify-icon></button></form></div></div></div>
@endsection
