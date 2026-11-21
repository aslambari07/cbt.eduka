@extends('layouts.auth')

@section('content')
<div class="flex min-h-screen items-center justify-center px-6">
    <div class="w-full max-w-md">
        <a href="{{ route('login') }}" class="cbt-brand"><span class="cbt-mark">C</span><span>CBT<span class="text-orange">.EDUKA</span></span></a>
        <div class="cbt-card mt-10">
            <p class="cbt-eyebrow">PEMULIHAN AKUN</p>
            <h1 class="mt-3 text-3xl font-semibold">Atur kata sandi baru</h1>
            <p class="mt-2 text-sm leading-6 text-muted">Gunakan kata sandi minimal 8 karakter untuk mengamankan akunmu.</p>
            <form action="{{ route('password.update') }}" method="post" class="mt-6 space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <label for="reset-email" class="cbt-label">Alamat email</label>
                <input id="reset-email" name="email" type="email" value="{{ old('email', $email) }}" autocomplete="email" class="cbt-input @error('email') border-error @enderror" required>
                @error('email')<p class="cbt-error">{{ $message }}</p>@enderror
                <label for="reset-password" class="cbt-label">Kata sandi baru</label>
                <input id="reset-password" name="password" type="password" autocomplete="new-password" class="cbt-input @error('password') border-error @enderror" required>
                @error('password')<p class="cbt-error">{{ $message }}</p>@enderror
                <label for="reset-password-confirmation" class="cbt-label">Konfirmasi kata sandi</label>
                <input id="reset-password-confirmation" name="password_confirmation" type="password" autocomplete="new-password" class="cbt-input" required>
                <button class="cbt-button-primary h-12 w-full justify-center" type="submit">Simpan kata sandi <iconify-icon icon="lucide:arrow-right"></iconify-icon></button>
            </form>
        </div>
    </div>
</div>
@endsection
