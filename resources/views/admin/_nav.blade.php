<aside class="hidden w-[238px] shrink-0 border-r border-line bg-[#090909] px-5 py-7 lg:flex lg:flex-col">
    <a href="{{ route('dashboard') }}" class="cbt-brand">
        <span class="cbt-mark">C</span>
        <span>CBT<span class="text-orange">.EDUKA</span></span>
    </a>

    <p class="mt-14 px-3 font-mono text-[10px] tracking-[.2em] text-muted">ADMIN CONSOLE</p>
    <nav class="mt-4 space-y-1" aria-label="Navigasi admin">
        @php($dashboardActive = request()->routeIs('dashboard'))
        <a class="{{ $dashboardActive ? 'flex items-center gap-3 rounded-lg bg-orange/10 px-3 py-3 text-sm text-orange' : 'cbt-link flex items-center gap-3 rounded-lg px-3 py-3 text-sm' }}" href="{{ route('dashboard') }}"><iconify-icon icon="lucide:layout-dashboard"></iconify-icon>Dashboard</a>
        <a class="{{ request()->routeIs('admin.users.*') ? 'flex items-center gap-3 rounded-lg bg-orange/10 px-3 py-3 text-sm text-orange' : 'cbt-link flex items-center gap-3 rounded-lg px-3 py-3 text-sm' }}" href="{{ route('admin.users.index') }}"><iconify-icon icon="lucide:users-round"></iconify-icon>Pengguna</a>
        <a class="{{ request()->routeIs('admin.institutions.*') ? 'flex items-center gap-3 rounded-lg bg-orange/10 px-3 py-3 text-sm text-orange' : 'cbt-link flex items-center gap-3 rounded-lg px-3 py-3 text-sm' }}" href="{{ route('admin.institutions.index') }}"><iconify-icon icon="lucide:building-2"></iconify-icon>Institusi</a>
        <a class="{{ request()->routeIs('admin.classes.*') ? 'flex items-center gap-3 rounded-lg bg-orange/10 px-3 py-3 text-sm text-orange' : 'cbt-link flex items-center gap-3 rounded-lg px-3 py-3 text-sm' }}" href="{{ route('admin.classes.index') }}"><iconify-icon icon="lucide:users-round"></iconify-icon>Kelas</a>
        <a class="{{ request()->routeIs('admin.subjects.*') ? 'flex items-center gap-3 rounded-lg bg-orange/10 px-3 py-3 text-sm text-orange' : 'cbt-link flex items-center gap-3 rounded-lg px-3 py-3 text-sm' }}" href="{{ route('admin.subjects.index') }}"><iconify-icon icon="lucide:book-open"></iconify-icon>Mata pelajaran</a>
        <a class="{{ request()->routeIs('teacher.exams.*') ? 'flex items-center gap-3 rounded-lg bg-orange/10 px-3 py-3 text-sm text-orange' : 'cbt-link flex items-center gap-3 rounded-lg px-3 py-3 text-sm' }}" href="{{ route('teacher.exams.index') }}"><iconify-icon icon="lucide:clipboard-list"></iconify-icon>Ujian</a>
        <a class="{{ request()->routeIs('teacher.questions.*') ? 'flex items-center gap-3 rounded-lg bg-orange/10 px-3 py-3 text-sm text-orange' : 'cbt-link flex items-center gap-3 rounded-lg px-3 py-3 text-sm' }}" href="{{ route('teacher.questions.index') }}"><iconify-icon icon="lucide:library-big"></iconify-icon>Bank soal</a>
        <a class="{{ request()->routeIs('admin.audit.*') ? 'flex items-center gap-3 rounded-lg bg-orange/10 px-3 py-3 text-sm text-orange' : 'cbt-link flex items-center gap-3 rounded-lg px-3 py-3 text-sm' }}" href="{{ route('admin.audit.index') }}"><iconify-icon icon="lucide:shield-check"></iconify-icon>Audit log</a>
        <a class="{{ request()->routeIs('admin.settings.*') ? 'flex items-center gap-3 rounded-lg bg-orange/10 px-3 py-3 text-sm text-orange' : 'cbt-link flex items-center gap-3 rounded-lg px-3 py-3 text-sm' }}" href="{{ route('admin.settings.edit') }}"><iconify-icon icon="lucide:settings"></iconify-icon>Pengaturan</a>
    </nav>

    <div class="mt-auto border-t border-line pt-5 text-xs text-muted">
        {{ auth()->user()->name }}<br>
        <span class="font-mono text-[10px]">ADMINISTRATOR</span>
        <form class="mt-5" method="post" action="{{ route('logout') }}" data-logout-form>
            @csrf
            <button type="submit" class="cbt-sidebar-link w-full text-left text-error"><iconify-icon icon="lucide:log-out"></iconify-icon> Keluar</button>
        </form>
    </div>
</aside>
