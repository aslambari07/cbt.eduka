@auth
    @php($user = auth()->user())
    @unless(request()->routeIs('landing') || request()->routeIs('student.sessions.*'))
        <div class="relative z-40 border-b border-line bg-[#090909] lg:hidden" data-mobile-nav>
            <header class="flex min-h-16 items-center justify-between px-4">
                <a href="{{ route('dashboard') }}" class="cbt-brand" aria-label="Buka dashboard CBT.EDUKA">
                    <span class="cbt-mark h-8 w-8 text-xs">C</span>
                    <span>CBT<span class="text-orange">.EDUKA</span></span>
                </a>
                <div class="flex items-center gap-3">
                    <span class="font-mono text-[9px] uppercase tracking-[.16em] text-muted">{{ $user->role->value }}</span>
                    <button type="button" class="flex h-10 w-10 items-center justify-center rounded-lg border border-line text-muted" aria-expanded="false" aria-controls="authenticated-mobile-menu" data-mobile-menu-toggle>
                        <iconify-icon icon="lucide:menu" data-mobile-menu-icon></iconify-icon>
                        <span class="sr-only">Buka menu navigasi</span>
                    </button>
                </div>
            </header>

            <div id="authenticated-mobile-menu" class="hidden border-t border-line bg-[#111111] px-4 py-4" data-mobile-menu-panel>
                <nav class="space-y-1" aria-label="Navigasi mobile">
                    <a href="{{ route('dashboard') }}" class="cbt-mobile-link {{ request()->routeIs('dashboard') ? 'cbt-mobile-link-active' : '' }}"><iconify-icon icon="lucide:layout-dashboard"></iconify-icon> Dashboard</a>
                    @if($user->hasRole(['admin', 'teacher']))
                        <a href="{{ route('teacher.exams.index') }}" class="cbt-mobile-link {{ request()->routeIs('teacher.exams.*') ? 'cbt-mobile-link-active' : '' }}"><iconify-icon icon="lucide:clipboard-list"></iconify-icon> Ujian</a>
                        <a href="{{ route('teacher.questions.index') }}" class="cbt-mobile-link {{ request()->routeIs('teacher.questions.*') ? 'cbt-mobile-link-active' : '' }}"><iconify-icon icon="lucide:library-big"></iconify-icon> Bank soal</a>
                    @endif
                    @if($user->hasRole('teacher'))
                        <a href="{{ route('teacher.classes.index') }}" class="cbt-mobile-link {{ request()->routeIs('teacher.classes.*') ? 'cbt-mobile-link-active' : '' }}"><iconify-icon icon="lucide:users-round"></iconify-icon> Kelas</a>
                        <a href="{{ route('teacher.ai.index') }}" class="cbt-mobile-link {{ request()->routeIs('teacher.ai.*') ? 'cbt-mobile-link-active' : '' }}"><iconify-icon icon="lucide:sparkles"></iconify-icon> AI Generator</a>
                    @endif
                    @if($user->hasRole('student'))
                        <a href="{{ route('student.exams.index') }}" class="cbt-mobile-link {{ request()->routeIs('student.exams.*') ? 'cbt-mobile-link-active' : '' }}"><iconify-icon icon="lucide:clipboard-list"></iconify-icon> Ujian saya</a>
                        <a href="{{ route('student.results.index') }}" class="cbt-mobile-link {{ request()->routeIs('student.results.*') ? 'cbt-mobile-link-active' : '' }}"><iconify-icon icon="lucide:bar-chart-3"></iconify-icon> Hasil & analitik</a>
                    @endif
                    @if($user->hasRole('admin'))
                        <a href="{{ route('admin.users.index') }}" class="cbt-mobile-link {{ request()->routeIs('admin.users.*') ? 'cbt-mobile-link-active' : '' }}"><iconify-icon icon="lucide:users-round"></iconify-icon> Pengguna</a>
                        <a href="{{ route('admin.settings.edit') }}" class="cbt-mobile-link {{ request()->routeIs('admin.settings.*') ? 'cbt-mobile-link-active' : '' }}"><iconify-icon icon="lucide:settings"></iconify-icon> Pengaturan</a>
                    @endif
                </nav>
                <div class="mt-4 border-t border-line pt-4">
                    <div class="mb-3 flex items-center gap-3 px-3 text-xs">
                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-orange/15 font-semibold text-orange">{{ str($user->name)->explode(' ')->map(fn (string $part): string => strtoupper(substr($part, 0, 1)))->take(2)->implode('') }}</span>
                        <span class="min-w-0"><strong class="block truncate font-medium text-white">{{ $user->name }}</strong><small class="text-muted">{{ $user->email }}</small></span>
                    </div>
                    <form method="post" action="{{ route('logout') }}" data-logout-form>
                        @csrf
                        <button type="submit" class="cbt-mobile-link w-full text-left text-error"><iconify-icon icon="lucide:log-out"></iconify-icon> Keluar</button>
                    </form>
                </div>
            </div>
        </div>
    @endunless
@endauth
