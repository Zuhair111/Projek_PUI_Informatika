<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>{{ $title ?? 'Dashboard Administrator' }}</title>
  <meta name="csrf-token" content="{{ csrf_token() }}">

  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

  <script src="https://cdn.tailwindcss.com"></script>

  <script src="https://unpkg.com/lucide@latest"></script>

  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; }
  </style>

  @stack('head')
</head>
<body class="min-h-screen bg-slate-50 text-gray-800 antialiased selection:bg-indigo-100 selection:text-indigo-900">
  <div class="min-h-screen lg:flex">

    {{-- ─── Sidebar ────────────────────────────────────── --}}
    <aside class="flex w-full flex-col border-r border-gray-200/70 bg-white lg:fixed lg:inset-y-0 lg:z-50 lg:w-72">
      {{-- Logo --}}
      <div class="flex h-16 shrink-0 items-center gap-3 border-b border-gray-100 px-5">
        <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-indigo-600 text-white shadow-sm shadow-indigo-300">
          <i data-lucide="shield-check" class="h-[18px] w-[18px]"></i>
        </div>
        <div>
          <h1 class="text-sm font-bold leading-none text-gray-900 tracking-tight">PresensiAman</h1>
          <p class="mt-0.5 text-[10px] font-semibold tracking-widest text-indigo-500 uppercase">Attendance Security</p>
        </div>
      </div>

      {{-- Navigation --}}
      <nav class="flex-1 overflow-y-auto px-3 py-5">
        <p class="mb-3 px-3 text-[10px] font-bold uppercase tracking-widest text-gray-400">Menu Utama</p>

        <div class="space-y-0.5">
          <a href="{{ route('dashboard.index') }}"
            class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all
            {{ request()->routeIs('dashboard.index')
              ? 'bg-indigo-50 text-indigo-700'
              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            @if(request()->routeIs('dashboard.index'))
              <span class="absolute inset-y-2 left-0 w-[3px] rounded-r-full bg-indigo-500"></span>
            @endif
            <i data-lucide="layout-dashboard" class="h-[18px] w-[18px] shrink-0 transition-colors
              {{ request()->routeIs('dashboard.index') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-600' }}"></i>
            Dashboard
          </a>

          <a href="{{ route('dashboard.karyawan.index') }}"
            class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all
            {{ request()->routeIs('dashboard.karyawan.*')
              ? 'bg-indigo-50 text-indigo-700'
              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            @if(request()->routeIs('dashboard.karyawan.*'))
              <span class="absolute inset-y-2 left-0 w-[3px] rounded-r-full bg-indigo-500"></span>
            @endif
            <i data-lucide="users" class="h-[18px] w-[18px] shrink-0 transition-colors
              {{ request()->routeIs('dashboard.karyawan.*') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-600' }}"></i>
            Manajemen Karyawan
          </a>

          <a href="{{ route('dashboard.geofencing.index') }}"
            class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all
            {{ request()->routeIs('dashboard.geofencing.*')
              ? 'bg-indigo-50 text-indigo-700'
              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            @if(request()->routeIs('dashboard.geofencing.*'))
              <span class="absolute inset-y-2 left-0 w-[3px] rounded-r-full bg-indigo-500"></span>
            @endif
            <i data-lucide="map-pin" class="h-[18px] w-[18px] shrink-0 transition-colors
              {{ request()->routeIs('dashboard.geofencing.*') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-600' }}"></i>
            Geofencing
          </a>

          <a href="{{ route('dashboard.rekap.index') }}"
            class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all
            {{ request()->routeIs('dashboard.rekap.*')
              ? 'bg-indigo-50 text-indigo-700'
              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            @if(request()->routeIs('dashboard.rekap.*'))
              <span class="absolute inset-y-2 left-0 w-[3px] rounded-r-full bg-indigo-500"></span>
            @endif
            <i data-lucide="calendar-check" class="h-[18px] w-[18px] shrink-0 transition-colors
              {{ request()->routeIs('dashboard.rekap.*') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-600' }}"></i>
            Rekap Presensi
          </a>

          <a href="{{ route('dashboard.log-deteksi.index') }}"
            class="group relative flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-all
            {{ request()->routeIs('dashboard.log-deteksi.*')
              ? 'bg-indigo-50 text-indigo-700'
              : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900' }}">
            @if(request()->routeIs('dashboard.log-deteksi.*'))
              <span class="absolute inset-y-2 left-0 w-[3px] rounded-r-full bg-indigo-500"></span>
            @endif
            <i data-lucide="shield-alert" class="h-[18px] w-[18px] shrink-0 transition-colors
              {{ request()->routeIs('dashboard.log-deteksi.*') ? 'text-indigo-600' : 'text-gray-400 group-hover:text-gray-600' }}"></i>
            Log Mock Location
          </a>
        </div>

      </nav>

      {{-- Sidebar Footer: User + Logout --}}
      <div class="border-t border-gray-100 p-3 space-y-1">
        <div class="flex items-center gap-3 rounded-xl bg-gray-50 px-3 py-2.5">
          <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white">
            {{ substr(auth()->user()->nama ?? 'A', 0, 1) }}
          </div>
          <div class="min-w-0 flex-1">
            <p class="truncate text-sm font-semibold leading-none text-gray-900">{{ auth()->user()->nama ?? 'Administrator' }}</p>
            <p class="mt-0.5 text-[11px] text-gray-500">Administrator</p>
          </div>
        </div>
        <form method="POST" action="{{ route('dashboard.logout') }}">
          @csrf
          <button class="flex w-full items-center gap-2.5 rounded-xl px-3 py-2 text-sm font-medium text-gray-500 transition-colors hover:bg-rose-50 hover:text-rose-600">
            <i data-lucide="log-out" class="h-4 w-4"></i>
            Keluar dari Akun
          </button>
        </form>
      </div>
    </aside>

    {{-- ─── Main Area ──────────────────────────────────── --}}
    <div class="flex min-h-screen flex-1 flex-col lg:pl-72">

      {{-- Header --}}
      <header class="sticky top-0 z-40 flex h-16 shrink-0 items-center justify-between border-b border-gray-200/70 bg-white/80 px-4 shadow-sm backdrop-blur-md sm:px-6 lg:px-8">
        <div>
          <h2 class="text-base font-bold leading-none text-gray-900">{{ $title ?? 'Dashboard' }}</h2>
          <p class="mt-0.5 text-xs text-gray-400">{{ now()->locale('id')->isoFormat('dddd, D MMMM Y') }}</p>
        </div>
        <div class="flex items-center gap-2">
          <button class="relative rounded-xl p-2 text-gray-500 transition-colors hover:bg-gray-100 hover:text-gray-700">
            <i data-lucide="bell" class="h-5 w-5"></i>
          </button>
          <div class="flex items-center gap-2.5 border-l border-gray-200 pl-3">
            <div class="flex h-8 w-8 shrink-0 items-center justify-center rounded-full bg-indigo-600 text-xs font-bold text-white ring-2 ring-white ring-offset-1">
              {{ substr(auth()->user()->nama ?? 'A', 0, 1) }}
            </div>
            <div class="hidden sm:block">
              <p class="text-sm font-semibold leading-none text-gray-800">{{ auth()->user()->nama ?? 'Administrator' }}</p>
              <p class="mt-0.5 text-xs text-gray-400">Admin</p>
            </div>
          </div>
        </div>
      </header>

      {{-- Content --}}
      <main class="mx-auto w-full max-w-7xl flex-1 p-4 sm:p-6 lg:p-8">
        @if (session('success'))
          <div class="relative mb-6 flex items-start gap-3 overflow-hidden rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-sm text-emerald-800 shadow-sm">
            <div class="absolute inset-y-0 left-0 w-1 bg-emerald-500"></div>
            <i data-lucide="check-circle-2" class="mt-0.5 h-4 w-4 shrink-0 text-emerald-600"></i>
            <span class="font-medium">{{ session('success') }}</span>
          </div>
        @endif

        @if ($errors->any())
          <div class="relative mb-6 flex items-start gap-3 overflow-hidden rounded-2xl border border-rose-200 bg-rose-50 p-4 text-sm text-rose-800 shadow-sm">
            <div class="absolute inset-y-0 left-0 w-1 bg-rose-500"></div>
            <i data-lucide="alert-circle" class="mt-0.5 h-4 w-4 shrink-0 text-rose-600"></i>
            <span class="font-medium">{{ $errors->first() }}</span>
          </div>
        @endif

        @yield('content')
      </main>
    </div>
  </div>

  <script>lucide.createIcons();</script>
  @stack('scripts')
</body>
</html>
