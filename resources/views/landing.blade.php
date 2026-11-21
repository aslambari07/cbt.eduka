@extends('layouts.app')

@section('content')
    <div class="landing-shell min-h-screen overflow-hidden font-sans text-white">
        <div class="landing-noise pointer-events-none fixed inset-0 z-0 opacity-30"></div>
        <div class="landing-lines pointer-events-none fixed inset-0 z-0"></div>

        <header class="landing-nav sticky top-0 z-50 border-b border-line">
            <div class="mx-auto flex min-h-20 max-w-[1360px] items-center justify-between px-5 sm:px-8 lg:px-10">
                <a href="#top" class="cbt-brand shrink-0" aria-label="CBT.EDUKA beranda">
                    <span class="cbt-mark">C</span>
                    <span>CBT<span class="text-orange">.EDUKA</span></span>
                </a>

                <nav class="hidden items-center gap-7 text-sm md:flex" aria-label="Navigasi utama">
                    <a href="#top" class="landing-nav-link" data-landing-nav-link>Beranda</a>
                    <a href="#features" class="landing-nav-link" data-landing-nav-link>Fitur</a>
                    <a href="#workflow" class="landing-nav-link" data-landing-nav-link>Cara kerja</a>
                    <a href="#teacher" class="landing-nav-link" data-landing-nav-link>Untuk guru</a>
                    <a href="#student" class="landing-nav-link" data-landing-nav-link>Untuk siswa</a>
                    <a href="#faq" class="landing-nav-link" data-landing-nav-link>FAQ</a>
                </nav>

                <div class="hidden items-center gap-4 md:flex">
                    @auth
                        <a href="{{ route('dashboard') }}" class="text-sm text-muted transition hover:text-white">Buka dashboard</a>
                        <form method="post" action="{{ route('logout') }}" data-logout-form>
                            @csrf
                            <button type="submit" class="cbt-button-primary min-h-11">Keluar <iconify-icon icon="lucide:log-out"></iconify-icon></button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="text-sm text-muted transition hover:text-white">Masuk</a>
                        <a href="{{ route('register') }}" class="cbt-button-primary min-h-11">Daftar akun <iconify-icon icon="lucide:arrow-up-right"></iconify-icon></a>
                    @endauth
                </div>

                <button type="button" class="flex h-11 w-11 items-center justify-center rounded-lg border border-line text-muted md:hidden" aria-expanded="false" aria-controls="landing-mobile-menu" aria-label="Buka menu navigasi" data-landing-menu-toggle>
                    <iconify-icon icon="lucide:menu" data-landing-menu-icon></iconify-icon>
                </button>
            </div>

            <div id="landing-mobile-menu" class="hidden border-t border-line bg-[#090909] px-5 py-4 md:hidden" data-landing-menu-panel>
                <nav class="space-y-1" aria-label="Navigasi mobile landing page">
                    <a href="#top" class="cbt-mobile-link">Beranda</a>
                    <a href="#features" class="cbt-mobile-link">Fitur</a>
                    <a href="#workflow" class="cbt-mobile-link">Cara kerja</a>
                    <a href="#teacher" class="cbt-mobile-link">Untuk guru</a>
                    <a href="#student" class="cbt-mobile-link">Untuk siswa</a>
                    <a href="#faq" class="cbt-mobile-link">FAQ</a>
                </nav>
                <div class="mt-4 grid gap-2 border-t border-line pt-4 sm:grid-cols-2">
                    @auth
                        <a href="{{ route('dashboard') }}" class="cbt-button-secondary min-h-11 justify-center">Buka dashboard</a>
                        <form method="post" action="{{ route('logout') }}" data-logout-form>
                            @csrf
                            <button type="submit" class="cbt-button-primary min-h-11 w-full justify-center">Keluar <iconify-icon icon="lucide:log-out"></iconify-icon></button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="cbt-button-secondary min-h-11 justify-center">Masuk</a>
                        <a href="{{ route('register') }}" class="cbt-button-primary min-h-11 justify-center">Daftar akun <iconify-icon icon="lucide:arrow-up-right"></iconify-icon></a>
                    @endauth
                </div>
            </div>
        </header>

        <main id="top" class="relative z-10">
            <section class="landing-hero mx-auto grid max-w-[1360px] items-center gap-12 px-5 pb-16 pt-12 sm:px-8 lg:grid-cols-12 lg:gap-8 lg:px-10 lg:pt-16">
                <div class="landing-reveal lg:col-span-7">
                    <p class="cbt-eyebrow"><span></span> PLATFORM COMPUTER BASED TEST</p>
                    <h1 class="mt-7 max-w-3xl font-serif text-[clamp(2.75rem,6vw,5rem)] font-bold leading-[1.02] tracking-[-.055em]">Ujian Online yang <span class="text-orange">Jujur, Cepat,</span> dan Terukur.</h1>
                    <p class="mt-7 max-w-2xl text-base leading-8 text-muted sm:text-lg">Platform Computer Based Test yang membantu guru menyelenggarakan ujian secara efisien dan membantu siswa mengikuti evaluasi pembelajaran dengan pengalaman yang mudah, aman, dan terstruktur.</p>
                    <div class="mt-9 flex flex-col gap-3 sm:flex-row">
                        @auth
                            <a href="{{ route('dashboard') }}" class="cbt-button-primary min-h-12 justify-center px-5">Buka dashboard <iconify-icon icon="lucide:arrow-right"></iconify-icon></a>
                        @else
                            <a href="{{ route('register') }}" class="cbt-button-primary min-h-12 justify-center px-5">Mulai sekarang <iconify-icon icon="lucide:arrow-right"></iconify-icon></a>
                        @endauth
                        <a href="#features" class="cbt-button-secondary min-h-12 justify-center px-5">Jelajahi fitur <iconify-icon icon="lucide:arrow-down" class="text-orange"></iconify-icon></a>
                    </div>
                    <div class="mt-12 grid max-w-xl grid-cols-3 gap-4 border-t border-line pt-5 text-[10px] text-muted sm:gap-7">
                        <span class="cbt-proof"><i class="bg-orange"></i> 8 TIPE SOAL</span>
                        <span class="cbt-proof"><i class="bg-success"></i> AUTOSAVE</span>
                        <span class="cbt-proof"><i class="bg-info"></i> TIMER SERVER</span>
                    </div>
                </div>

                <div class="relative min-h-[500px] lg:col-span-5">
                    <div class="landing-orbit absolute -right-16 -top-10 h-[460px] w-[460px]"></div>
                    <div class="landing-orbit absolute -right-4 top-4 h-[370px] w-[370px] border-line"></div>
                    <div class="landing-sphere landing-float absolute right-8 top-7 h-44 w-44 rounded-full sm:right-20 sm:h-52 sm:w-52"></div>
                    <div class="absolute right-10 top-48 h-52 w-52 rounded-full bg-orange/10 blur-3xl"></div>

                    <div class="landing-ui absolute left-0 top-44 z-20 w-[min(100%,22rem)] rounded-xl border border-line p-5 sm:left-1 sm:w-[22rem]">
                        <div class="flex items-center justify-between border-b border-line pb-4">
                            <div class="cbt-brand text-xs"><span class="cbt-mark h-7 w-7 text-[10px]">C</span><span>CBT<span class="text-orange">.EDUKA</span></span></div>
                            <span class="font-mono text-[10px] text-orange">15 / 30</span>
                        </div>
                        <p class="mt-5 font-mono text-[9px] text-muted">SOAL PILIHAN GANDA</p>
                        <h2 class="mt-2 font-serif text-lg font-bold leading-snug">Jika x² + 3x − 10 = 0, maka nilai x adalah...</h2>
                        <div class="mt-4 space-y-2 text-[11px]">
                            <div class="landing-ui-row-selected flex items-center gap-2 rounded px-3 py-2"><b class="flex h-5 w-5 items-center justify-center rounded-full bg-orange text-[9px]">B</b> x = 3 atau x = −2 <iconify-icon icon="lucide:check-circle-2" class="ml-auto text-orange"></iconify-icon></div>
                            <div class="landing-ui-row flex items-center gap-2 rounded px-3 py-2 text-muted"><b class="flex h-5 w-5 items-center justify-center rounded-full border border-line text-[9px]">C</b> x = −3 atau x = 2</div>
                        </div>
                        <div class="mt-5 flex items-center justify-between border-t border-line pt-4"><span class="font-mono text-[9px] text-success">● TERSIMPAN</span><span class="font-mono text-[10px] text-orange">23:45</span></div>
                    </div>

                    <div class="landing-ui absolute right-0 top-[20rem] z-10 w-44 rounded-lg border border-white/10 bg-elevated p-4 sm:right-1">
                        <div class="flex items-center justify-between font-mono text-[9px] text-muted">HASIL UJIAN <span class="h-2 w-2 rounded-full bg-success"></span></div>
                        <p class="mt-4 font-mono text-3xl font-semibold text-white">92<span class="text-sm text-muted">/100</span></p>
                        <p class="mt-1 text-[10px] text-success">Lulus · hasil dipublikasikan</p>
                        <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-line"><div class="h-full w-[92%] rounded-full bg-indigo"></div></div>
                    </div>

                    <p class="absolute bottom-0 left-0 font-mono text-[10px] tracking-[.2em] text-muted">PRECISION / CLARITY / TRUST</p>
                </div>
            </section>

            <section id="platform" class="border-y border-line bg-[#090909]">
                <div class="mx-auto grid max-w-[1360px] gap-8 px-5 py-16 sm:px-8 lg:grid-cols-[.8fr_1.2fr] lg:px-10 lg:py-24">
                    <div>
                        <p class="cbt-eyebrow">01 / PLATFORM</p>
                        <h2 class="mt-4 max-w-lg font-serif text-3xl font-bold tracking-tight sm:text-4xl">Satu sistem untuk ujian yang lebih terarah.</h2>
                    </div>
                    <div class="grid gap-8 text-sm leading-7 text-muted sm:grid-cols-2">
                        <p>CBT.EDUKA menyatukan penyusunan soal, pelaksanaan ujian, penyimpanan jawaban, penilaian, dan hasil dalam satu alur yang dapat dilacak.</p>
                        <p>Guru mendapat ruang kerja untuk mengatur evaluasi. Siswa mendapat antarmuka yang fokus, responsif, dan mengikuti instruksi ujian dengan jelas.</p>
                    </div>
                </div>
            </section>

            <section id="features" class="mx-auto max-w-[1360px] px-5 py-16 sm:px-8 lg:px-10 lg:py-24">
                <div class="flex flex-col gap-5 border-b border-line pb-8 sm:flex-row sm:items-end sm:justify-between">
                    <div><p class="cbt-eyebrow">02 / FITUR</p><h2 class="mt-4 font-serif text-3xl font-bold tracking-tight sm:text-4xl">Alur ujian dari awal sampai hasil.</h2></div>
                    <p class="max-w-md text-sm leading-7 text-muted">Setiap bagian hadir karena memang dibutuhkan dalam operasional ujian, bukan sebagai hiasan dashboard.</p>
                </div>
                <div class="mt-8 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                    @foreach([
                        ['clipboard-list', 'Manajemen ujian', 'Susun informasi, instruksi, jadwal, kelas, dan status ujian dari satu ruang kerja.'],
                        ['library-big', 'Bank soal', 'Kelola soal, kategori, tag, pembahasan, bobot, dan tingkat kesulitan.'],
                        ['layers-3', '8 tipe soal', 'Pilihan ganda, multi-select, benar-salah, singkat, esai, matching, ordering, dan fill blank.'],
                        ['calendar-clock', 'Penjadwalan', 'Tentukan waktu mulai, batas selesai, durasi, percobaan, dan publikasi hasil.'],
                        ['users-round', 'Assignment siswa', 'Hubungkan ujian dengan kelas dan peserta sesuai tanggung jawab pengajar.'],
                        ['timer', 'Timer server-side', 'Waktu sesi berasal dari server dan divalidasi pada setiap aksi penting.'],
                        ['cloud-check', 'Autosave jawaban', 'Jawaban tersimpan ke server saat berubah dengan status penyimpanan yang jelas.'],
                        ['shuffle', 'Randomisasi soal', 'Urutan soal dan opsi dapat diacak lalu dipertahankan selama sesi.'],
                        ['flag', 'Penanda soal', 'Tandai pertanyaan yang perlu ditinjau dan temukan kembali melalui navigator.'],
                        ['send', 'Submission aman', 'Finalisasi jawaban idempotent dan mengunci perubahan setelah dikirim.'],
                        ['check-check', 'Penilaian', 'Penilaian otomatis untuk tipe soal terstruktur dan penilaian manual untuk esai.'],
                        ['bar-chart-3', 'Hasil & analitik', 'Lihat nilai, breakdown, performa tipe soal, dan rekomendasi belajar.'],
                        ['award', 'Sertifikat', 'Akses sertifikat yang diterbitkan untuk hasil ujian yang memenuhi ketentuan.'],
                        ['bell', 'Notifikasi', 'Terima informasi tentang ujian, hasil, jadwal, dan pengumuman yang relevan.'],
                        ['shield-check', 'Audit log', 'Admin dapat meninjau aktivitas penting dan perubahan yang perlu ditelusuri.'],
                    ] as $feature)
                        <article class="landing-grid-card rounded-xl p-5 transition-transform duration-200">
                            <span class="landing-feature-icon"><iconify-icon icon="lucide:{{ $feature[0] }}"></iconify-icon></span>
                            <h3 class="mt-5 text-sm font-semibold">{{ $feature[1] }}</h3>
                            <p class="mt-2 text-xs leading-6 text-muted">{{ $feature[2] }}</p>
                        </article>
                    @endforeach
                </div>
            </section>

            <section id="teacher" class="border-y border-line bg-[#0b0b0b]">
                <div class="mx-auto grid max-w-[1360px] items-center gap-12 px-5 py-16 sm:px-8 lg:grid-cols-2 lg:px-10 lg:py-24">
                    <div>
                        <p class="cbt-eyebrow">03 / UNTUK GURU</p>
                        <h2 class="mt-4 max-w-xl font-serif text-3xl font-bold tracking-tight sm:text-4xl">Ruang kerja untuk menyiapkan evaluasi.</h2>
                        <p class="mt-5 max-w-xl text-sm leading-7 text-muted">Dari bank soal sampai analitik, guru dapat menjaga alur ujian tetap rapi dan mudah ditinjau.</p>
                        <a href="{{ route('register') }}" class="cbt-button-primary mt-8 min-h-11">Daftar sebagai pengguna <iconify-icon icon="lucide:arrow-up-right"></iconify-icon></a>
                    </div>
                    <div class="landing-ui rounded-2xl border border-line p-5 sm:p-7">
                        <div class="flex items-center justify-between border-b border-line pb-4"><div><p class="font-mono text-[10px] tracking-[.18em] text-orange">RUANG GURU</p><h3 class="mt-2 text-lg font-semibold">Ujian yang perlu diperhatikan</h3></div><span class="rounded-full bg-success/10 px-2.5 py-1 text-[10px] text-success">Data aktual</span></div>
                        <div class="mt-6 space-y-3">
                            @foreach([['Buat ujian', 'Informasi dasar · soal · jadwal', 'plus', 'orange'], ['Atur kelas', 'Assignment peserta dan akses', 'users-round', 'indigo'], ['Pantau hasil', 'Nilai, status, dan analitik', 'bar-chart-3', 'violet']] as $item)
                                <div class="flex items-center gap-3 rounded-lg border border-line bg-elevated p-3"><span class="flex h-9 w-9 items-center justify-center rounded-lg bg-{{ $item[3] }}/10 text-{{ $item[3] }}"><iconify-icon icon="lucide:{{ $item[2] }}"></iconify-icon></span><span class="flex-1"><strong class="block text-xs font-medium">{{ $item[0] }}</strong><small class="text-[10px] text-muted">{{ $item[1] }}</small></span><iconify-icon icon="lucide:arrow-up-right" class="text-muted"></iconify-icon></div>
                            @endforeach
                        </div>
                        <div class="mt-6 grid grid-cols-3 gap-3 border-t border-line pt-5 text-center"><div><p class="font-mono text-lg text-orange">DRAFT</p><p class="mt-1 text-[10px] text-muted">Simpan sementara</p></div><div><p class="font-mono text-lg text-indigo">LIVE</p><p class="mt-1 text-[10px] text-muted">Pantau sesi</p></div><div><p class="font-mono text-lg text-success">HASIL</p><p class="mt-1 text-[10px] text-muted">Tinjau nilai</p></div></div>
                    </div>
                </div>
            </section>

            <section id="student" class="mx-auto grid max-w-[1360px] items-center gap-12 px-5 py-16 sm:px-8 lg:grid-cols-[.9fr_1.1fr] lg:px-10 lg:py-24">
                <div class="order-2 lg:order-1">
                    <div class="mx-auto max-w-sm rounded-[1.75rem] border border-line bg-[#0d0d0d] p-3 shadow-[0_30px_90px_rgba(0,0,0,.45)]">
                        <div class="rounded-[1.25rem] border border-line bg-elevated p-4">
                            <div class="flex items-center justify-between border-b border-line pb-4"><div><p class="text-xs font-semibold">Ujian Matematika</p><p class="mt-1 font-mono text-[9px] text-muted">15 / 30 soal</p></div><span class="font-mono text-lg text-orange">23:45</span></div>
                            <div class="mt-4 h-1.5 overflow-hidden rounded-full bg-line"><div class="h-full w-1/2 rounded-full bg-orange"></div></div>
                            <p class="mt-6 font-serif text-lg font-bold leading-7">Jika x² + 3x − 10 = 0, maka nilai x adalah...</p>
                            <div class="mt-5 space-y-2"><div class="landing-ui-row flex min-h-12 items-center gap-3 rounded-lg px-3 text-xs text-muted"><span class="flex h-7 w-7 items-center justify-center rounded-full border border-line font-mono text-[10px]">A</span>x = 2 atau x = −5</div><div class="landing-ui-row-selected flex min-h-12 items-center gap-3 rounded-lg px-3 text-xs"><span class="flex h-7 w-7 items-center justify-center rounded-full bg-orange font-mono text-[10px] font-bold">B</span>x = 3 atau x = −2<iconify-icon icon="lucide:check-circle-2" class="ml-auto text-orange"></iconify-icon></div></div>
                            <div class="mt-5 flex items-center justify-center gap-2 border-t border-line pt-4 font-mono text-[9px] text-success"><iconify-icon icon="lucide:cloud-check"></iconify-icon> Tersimpan otomatis</div>
                        </div>
                    </div>
                </div>
                <div class="order-1 lg:order-2">
                    <p class="cbt-eyebrow">04 / UNTUK SISWA</p>
                    <h2 class="mt-4 max-w-xl font-serif text-3xl font-bold tracking-tight sm:text-4xl">Antarmuka ujian yang fokus pada jawaban.</h2>
                    <p class="mt-5 max-w-xl text-sm leading-7 text-muted">Siswa melihat jadwal, instruksi, progress, waktu tersisa, dan status jawaban dalam satu pengalaman yang mudah dipahami.</p>
                    <div class="mt-8 grid gap-4 sm:grid-cols-2">
                        @foreach([['log-in', 'Masuk ke akun', 'Akses ujian yang ditugaskan.'], ['book-open', 'Baca instruksi', 'Pahami aturan sebelum mulai.'], ['save', 'Jawaban tersimpan', 'Perubahan dikirim ke server.'], ['send', 'Kirim ujian', 'Finalisasi setelah semua siap.']] as $step)
                            <div class="flex gap-3"><span class="landing-feature-icon h-9 w-9 shrink-0"><iconify-icon icon="lucide:{{ $step[0] }}"></iconify-icon></span><div><h3 class="text-xs font-semibold">{{ $step[1] }}</h3><p class="mt-1 text-[11px] leading-5 text-muted">{{ $step[2] }}</p></div></div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="security" class="border-y border-line bg-[#090909]">
                <div class="mx-auto max-w-[1360px] px-5 py-16 sm:px-8 lg:px-10 lg:py-24">
                    <div class="grid gap-8 lg:grid-cols-[.8fr_1.2fr]"><div><p class="cbt-eyebrow">05 / KEAMANAN</p><h2 class="mt-4 max-w-lg font-serif text-3xl font-bold tracking-tight sm:text-4xl">Kontrol penting berada di server.</h2></div><p class="max-w-2xl text-sm leading-7 text-muted">CBT.EDUKA memvalidasi akses, waktu, jawaban, dan submission di backend. Browser membantu interaksi, tetapi bukan sumber kebenaran ujian.</p></div>
                    <div class="mt-10 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach([['clock-3', 'Server-authoritative timer', 'Batas waktu sesi diperiksa dari waktu server.'], ['lock-keyhole', 'Session ownership', 'Setiap sesi terhubung ke siswa dan assignment yang valid.'], ['refresh-cw', 'Autosave idempotent', 'Request berulang tidak membuat jawaban ganda atau tidak berurutan.'], ['shield-check', 'Role validation', 'Akses endpoint diperiksa melalui middleware dan policy.']] as $item)
                            <div class="border-t border-orange/50 pt-4"><iconify-icon icon="lucide:{{ $item[0] }}" class="text-orange"></iconify-icon><h3 class="mt-4 text-sm font-semibold">{{ $item[1] }}</h3><p class="mt-2 text-xs leading-6 text-muted">{{ $item[2] }}</p></div>
                        @endforeach
                    </div>
                </div>
            </section>

            <section id="workflow" class="mx-auto max-w-[1360px] px-5 py-16 sm:px-8 lg:px-10 lg:py-24">
                <div class="flex flex-col gap-4 border-b border-line pb-8 sm:flex-row sm:items-end sm:justify-between"><div><p class="cbt-eyebrow">06 / CARA KERJA</p><h2 class="mt-4 font-serif text-3xl font-bold tracking-tight sm:text-4xl">Dua alur, satu sistem.</h2></div><p class="max-w-md text-sm leading-7 text-muted">Peran guru dan siswa bertemu di titik yang sama: ujian yang dapat ditelusuri.</p></div>
                <div class="mt-10 grid gap-12 lg:grid-cols-2">
                    <div><div class="flex items-center gap-3"><span class="flex h-9 w-9 items-center justify-center rounded bg-orange font-mono text-xs font-bold">G</span><h3 class="text-lg font-semibold">Alur guru</h3></div><div class="mt-7 space-y-7"><div class="landing-step"><p class="font-mono text-[10px] text-orange">01</p><h4 class="mt-2 text-sm font-semibold">Kelola kelas dan bank soal</h4><p class="mt-2 text-xs leading-6 text-muted">Siapkan materi dan peserta yang menerima ujian.</p></div><div class="landing-step"><p class="font-mono text-[10px] text-orange">02</p><h4 class="mt-2 text-sm font-semibold">Bangun dan jadwalkan ujian</h4><p class="mt-2 text-xs leading-6 text-muted">Atur tipe soal, durasi, navigasi, dan publikasi.</p></div><div class="landing-step"><p class="font-mono text-[10px] text-orange">03</p><h4 class="mt-2 text-sm font-semibold">Pantau dan nilai</h4><p class="mt-2 text-xs leading-6 text-muted">Pantau sesi, nilai otomatis, dan tinjau jawaban esai.</p></div></div></div>
                    <div><div class="flex items-center gap-3"><span class="flex h-9 w-9 items-center justify-center rounded bg-indigo font-mono text-xs font-bold">S</span><h3 class="text-lg font-semibold">Alur siswa</h3></div><div class="mt-7 space-y-7"><div class="landing-step"><p class="font-mono text-[10px] text-indigo">01</p><h4 class="mt-2 text-sm font-semibold">Buka ujian tersedia</h4><p class="mt-2 text-xs leading-6 text-muted">Lihat jadwal, durasi, instruksi, dan status akses.</p></div><div class="landing-step"><p class="font-mono text-[10px] text-indigo">02</p><h4 class="mt-2 text-sm font-semibold">Kerjakan dengan navigator</h4><p class="mt-2 text-xs leading-6 text-muted">Pindah soal, tandai pertanyaan, dan lanjutkan sesi yang sah.</p></div><div class="landing-step"><p class="font-mono text-[10px] text-indigo">03</p><h4 class="mt-2 text-sm font-semibold">Kirim dan tinjau hasil</h4><p class="mt-2 text-xs leading-6 text-muted">Hasil terlihat mengikuti aturan publikasi dari ujian.</p></div></div></div>
                </div>
            </section>

            <section id="faq" class="border-y border-line bg-[#0b0b0b]">
                <div class="mx-auto grid max-w-[1360px] gap-10 px-5 py-16 sm:px-8 lg:grid-cols-[.7fr_1.3fr] lg:px-10 lg:py-24">
                    <div><p class="cbt-eyebrow">07 / FAQ</p><h2 class="mt-4 font-serif text-3xl font-bold tracking-tight sm:text-4xl">Pertanyaan tentang platform.</h2><p class="mt-5 max-w-sm text-sm leading-7 text-muted">Jawaban singkat berdasarkan alur yang tersedia di aplikasi saat ini.</p></div>
                    <div>
                        @foreach([
                            ['Apa itu CBT.EDUKA?', 'CBT.EDUKA adalah platform Computer Based Test untuk mengelola ujian, soal, sesi peserta, penilaian, dan hasil dalam satu aplikasi.'],
                            ['Siapa yang dapat menggunakan platform?', 'Admin mengelola ekosistem. Guru mengelola kelas, soal, ujian, dan hasil. Siswa mengikuti ujian yang ditugaskan kepadanya.'],
                            ['Bagaimana guru membuat ujian?', 'Guru masuk ke dashboard, memilih Buat ujian baru, lalu mengisi informasi dasar, soal, jadwal, keamanan, dan penilaian.'],
                            ['Bagaimana siswa mengikuti ujian?', 'Siswa login, membuka ujian yang tersedia, membaca instruksi, memulai sesi, mengerjakan soal, lalu mengirim jawaban.'],
                            ['Apakah jawaban tersimpan otomatis?', 'Ya. Perubahan jawaban dikirim ke server melalui endpoint autosave dan status penyimpanan ditampilkan pada antarmuka ujian.'],
                            ['Bagaimana penilaian dilakukan?', 'Pilihan ganda dan tipe terstruktur dinilai otomatis. Jawaban esai dapat ditinjau serta diberi nilai secara manual oleh guru.'],
                            ['Apakah ujian dapat dijadwalkan?', 'Ya. Guru dapat mengatur waktu mulai, waktu selesai, durasi, batas percobaan, dan aturan publikasi hasil.'],
                            ['Bagaimana melihat hasil ujian?', 'Siswa melihat hasil setelah status hasil dipublikasikan atau ketika aturan ujian mengizinkan hasil langsung.'],
                            ['Bagaimana jika koneksi internet terputus?', 'Antarmuka menampilkan status penyimpanan gagal atau koneksi. Siswa perlu menyambung kembali dan memastikan jawaban tersimpan sebelum mengirim.'],
                            ['Bagaimana cara login?', 'Pilih Masuk, isi email dan kata sandi, lalu sistem mengarahkan pengguna ke dashboard sesuai role.'],
                        ] as $faq)
                            <details class="landing-faq group"><summary>{{ $faq[0] }}<iconify-icon icon="lucide:plus" class="shrink-0"></iconify-icon></summary><p>{{ $faq[1] }}</p></details>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="mx-auto max-w-[1360px] px-5 py-16 sm:px-8 lg:px-10 lg:py-24">
                <div class="relative overflow-hidden rounded-2xl border border-orange/30 bg-orange/5 p-7 sm:p-10 lg:p-14">
                    <div class="absolute -right-20 -top-24 h-64 w-64 rounded-full border border-orange/20"></div>
                    <div class="relative max-w-2xl"><p class="cbt-eyebrow">08 / MULAI</p><h2 class="mt-4 font-serif text-3xl font-bold tracking-tight sm:text-4xl">Mulai Pengalaman Ujian Digital yang Lebih Terstruktur.</h2><p class="mt-5 text-sm leading-7 text-muted">Masuk ke ruang kerja CBT.EDUKA atau buat akun untuk mengikuti alur yang sesuai dengan role Anda.</p><div class="mt-8 flex flex-col gap-3 sm:flex-row"><a href="{{ route('login') }}" class="cbt-button-primary min-h-12 justify-center px-5">Masuk ke CBT.EDUKA <iconify-icon icon="lucide:arrow-right"></iconify-icon></a><a href="{{ route('register') }}" class="cbt-button-secondary min-h-12 justify-center px-5">Daftar akun <iconify-icon icon="lucide:arrow-up-right"></iconify-icon></a></div></div>
                </div>
            </section>
        </main>

        <footer class="relative z-10 border-t border-line bg-[#090909]">
            <div class="mx-auto max-w-[1360px] px-5 py-12 sm:px-8 lg:px-10">
                <div class="grid gap-10 md:grid-cols-[1.3fr_.7fr_.7fr]">
                    <div><a href="#top" class="cbt-brand"><span class="cbt-mark">C</span><span>CBT<span class="text-orange">.EDUKA</span></span></a><p class="mt-5 max-w-sm text-sm leading-7 text-muted">Platform Computer Based Test untuk ujian yang mudah dikelola, aman diikuti, dan dapat ditinjau.</p></div>
                    <div><p class="font-mono text-[10px] tracking-[.18em] text-orange">NAVIGASI</p><nav class="mt-4 grid gap-3 text-sm text-muted"><a href="#features" class="cbt-link">Fitur</a><a href="#workflow" class="cbt-link">Cara kerja</a><a href="#teacher" class="cbt-link">Untuk guru</a><a href="#student" class="cbt-link">Untuk siswa</a></nav></div>
                    <div><p class="font-mono text-[10px] tracking-[.18em] text-orange">AKSES</p><nav class="mt-4 grid gap-3 text-sm text-muted"><a href="{{ route('login') }}" class="cbt-link">Masuk</a><a href="{{ route('register') }}" class="cbt-link">Daftar akun</a><a href="#faq" class="cbt-link">FAQ</a></nav></div>
                </div>
                <div class="mt-10 flex flex-col gap-3 border-t border-line pt-5 font-mono text-[10px] text-muted sm:flex-row sm:items-center sm:justify-between"><span>CBT.EDUKA · {{ now()->year }}</span><span class="flex items-center gap-2"><i class="h-1.5 w-1.5 rounded-full bg-success"></i> PLATFORM SIAP DIGUNAKAN</span></div>
            </div>
        </footer>
    </div>
@endsection
