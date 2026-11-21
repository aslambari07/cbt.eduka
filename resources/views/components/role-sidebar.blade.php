@props(['role'])

@php($user = auth()->user())

<aside class="hidden w-[238px] shrink-0 border-r border-line bg-[#090909] px-5 py-7 lg:flex lg:flex-col">
    <a href="{{ route('dashboard') }}" class="cbt-brand" aria-label="Buka dashboard CBT.EDUKA">
        <span class="cbt-mark">C</span>
        <span>CBT<span class="text-orange">.EDUKA</span></span>
    </a>

    <div class="mt-14">
        <p class="mb-4 px-3 font-mono text-[10px] tracking-[.2em] text-muted">{{ $role === 'teacher' ? 'RUANG GURU' : 'RUANG SISWA' }}</p>
        <nav class="space-y-1" aria-label="Navigasi {{ $role === 'teacher' ? 'guru' : 'siswa' }}">
            <a class="{{ request()->routeIs('dashboard') ? 'cbt-sidebar-link-active' : 'cbt-sidebar-link' }}" href="{{ route('dashboard') }}"><iconify-icon icon="lucide:layout-dashboard"></iconify-icon> Ringkasan</a>
            @if($role === 'teacher')
                <a class="{{ request()->routeIs('teacher.exams.*') ? 'cbt-sidebar-link-active' : 'cbt-sidebar-link' }}" href="{{ route('teacher.exams.index') }}"><iconify-icon icon="lucide:clipboard-list"></iconify-icon> Ujian saya</a>
                <a class="{{ request()->routeIs('teacher.questions.*') ? 'cbt-sidebar-link-active' : 'cbt-sidebar-link' }}" href="{{ route('teacher.questions.index') }}"><iconify-icon icon="lucide:library-big"></iconify-icon> Bank soal</a>
                <a class="{{ request()->routeIs('teacher.classes.*') ? 'cbt-sidebar-link-active' : 'cbt-sidebar-link' }}" href="{{ route('teacher.classes.index') }}"><iconify-icon icon="lucide:users-round"></iconify-icon> Kelas</a>
                <a class="{{ request()->routeIs('teacher.ai.*') ? 'cbt-sidebar-link-active' : 'cbt-sidebar-link' }}" href="{{ route('teacher.ai.index') }}"><iconify-icon icon="lucide:sparkles"></iconify-icon> AI Generator</a>
            @else
                <a class="{{ request()->routeIs('student.exams.*') ? 'cbt-sidebar-link-active' : 'cbt-sidebar-link' }}" href="{{ route('student.exams.index') }}"><iconify-icon icon="lucide:clipboard-list"></iconify-icon> Ujian saya</a>
                <a class="{{ request()->routeIs('student.results.*') ? 'cbt-sidebar-link-active' : 'cbt-sidebar-link' }}" href="{{ route('student.results.index') }}"><iconify-icon icon="lucide:bar-chart-3"></iconify-icon> Hasil & analitik</a>
            @endif
        </nav>
    </div>

    <div class="mt-auto border-t border-line pt-5">
        <div class="flex items-center gap-3 px-2">
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-indigo/20 text-xs font-semibold text-indigo">{{ str($user->name)->explode(' ')->map(fn (string $part): string => strtoupper(substr($part, 0, 1)))->take(2)->implode('') }}</span>
            <div class="min-w-0">
                <p class="truncate text-xs font-medium">{{ $user->name }}</p>
                <p class="truncate text-[10px] text-muted">{{ $user->email }}</p>
            </div>
        </div>
        <form class="mt-5" method="post" action="{{ route('logout') }}" data-logout-form>
            @csrf
            <button type="submit" class="cbt-sidebar-link w-full text-left text-error"><iconify-icon icon="lucide:log-out"></iconify-icon> Keluar</button>
        </form>
    </div>
</aside>
