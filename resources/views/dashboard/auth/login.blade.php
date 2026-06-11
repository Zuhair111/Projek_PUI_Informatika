<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Masuk — PresensiAman</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; }
    .panel-bg {
      background-color: #312e81;
      background-image:
        radial-gradient(ellipse at 15% 85%, rgba(99,102,241,0.45) 0, transparent 55%),
        radial-gradient(ellipse at 85% 15%, rgba(67,56,202,0.6) 0, transparent 55%);
    }
    .input-field {
      width: 100%;
      border-radius: 0.75rem;
      border: 1px solid #e5e7eb;
      background: #f9fafb;
      padding: 0.75rem 1rem;
      font-size: 0.875rem;
      color: #111827;
      outline: none;
      transition: all 0.15s;
    }
    .input-field::placeholder { color: #9ca3af; }
    .input-field:focus {
      border-color: #6366f1;
      background: #fff;
      box-shadow: 0 0 0 4px rgba(99,102,241,0.08);
    }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4" style="background:#f1f5f9">
  <div class="w-full max-w-5xl">
    <div class="overflow-hidden rounded-3xl shadow-2xl grid lg:grid-cols-[11fr_9fr]">

      {{-- ─── Left Panel ─────────────────────────────── --}}
      <div class="panel-bg relative hidden lg:flex flex-col justify-between p-10 text-white">
        {{-- Logo --}}
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-white/20 bg-white/10">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
          </div>
          <div>
            <p class="font-bold text-sm leading-none">PresensiAman</p>
            <p class="mt-0.5 text-[10px] font-medium tracking-widest uppercase text-indigo-300">Attendance Security</p>
          </div>
        </div>

        {{-- Hero Text --}}
        <div>
          <h1 class="text-[2.4rem] font-bold leading-tight">Presensi Aman,<br>Tanpa Rekayasa.</h1>
          <p class="mt-4 max-w-xs text-sm leading-relaxed text-indigo-200">Platform manajemen kehadiran berbasis GPS dengan deteksi anti-kecurangan otomatis untuk bisnis Anda.</p>

          <div class="mt-8 space-y-4">
            <div class="flex items-start gap-3">
              <div class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-white/15 ring-1 ring-white/10">
                <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
              </div>
              <div>
                <p class="text-sm font-semibold">Deteksi Mock Location</p>
                <p class="mt-0.5 text-xs text-indigo-300">Blokir manipulasi GPS secara real-time.</p>
              </div>
            </div>
            <div class="flex items-start gap-3">
              <div class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-white/15 ring-1 ring-white/10">
                <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
              </div>
              <div>
                <p class="text-sm font-semibold">Geofencing Dinamis</p>
                <p class="mt-0.5 text-xs text-indigo-300">Atur zona presensi langsung dari peta.</p>
              </div>
            </div>
            <div class="flex items-start gap-3">
              <div class="mt-0.5 flex h-5 w-5 shrink-0 items-center justify-center rounded-full bg-white/15 ring-1 ring-white/10">
                <svg width="9" height="9" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
              </div>
              <div>
                <p class="text-sm font-semibold">Laporan Instan</p>
                <p class="mt-0.5 text-xs text-indigo-300">Export rekap PDF & Excel dengan sekali klik.</p>
              </div>
            </div>
          </div>
        </div>

        <p class="text-[11px] text-indigo-400/70">© {{ date('Y') }} PresensiAman. Hak cipta dilindungi.</p>
      </div>

      {{-- ─── Right Panel (Form) ──────────────────────── --}}
      <div class="flex flex-col justify-center bg-white p-8 sm:p-10">
        {{-- Mobile logo --}}
        <div class="mb-6 flex items-center gap-2 lg:hidden">
          <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-white">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
          </div>
          <span class="text-sm font-bold text-gray-900">PresensiAman</span>
        </div>

        <div class="mb-7">
          <h2 class="text-2xl font-bold text-gray-900">Masuk ke Dashboard</h2>
          <p class="mt-1.5 text-sm text-gray-500">Gunakan email dan password administrator Anda.</p>
        </div>

        @if ($errors->any())
          <div class="mb-5 flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <svg class="mt-0.5 h-4 w-4 shrink-0 text-rose-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span>{{ $errors->first() }}</span>
          </div>
        @endif

        <form method="POST" action="{{ route('dashboard.login.submit') }}" class="space-y-4">
          @csrf

          <div>
            <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700">Alamat Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
              placeholder="admin@perusahaan.com" class="input-field">
          </div>

          <div>
            <label for="password" class="mb-1.5 block text-sm font-medium text-gray-700">Password</label>
            <input id="password" name="password" type="password" required autocomplete="current-password"
              placeholder="••••••••" class="input-field">
          </div>

          <div class="pt-1">
            <button type="submit"
              class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition-all hover:bg-indigo-700 hover:shadow-md active:scale-[0.99]">
              Masuk Sekarang
            </button>
          </div>
        </form>

      </div>
    </div>
  </div>
</body>
</html>
