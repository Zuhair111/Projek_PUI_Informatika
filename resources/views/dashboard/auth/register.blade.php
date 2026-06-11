<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Registrasi — PUI Admin</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">
  <style>
    body { font-family: 'Plus Jakarta Sans', sans-serif; }
    .panel-bg {
      background-color: #312e81;
      background-image:
        radial-gradient(ellipse at 20% 80%, rgba(99,102,241,0.4) 0, transparent 55%),
        radial-gradient(ellipse at 80% 20%, rgba(67,56,202,0.6) 0, transparent 55%);
    }
    .input-field {
      width: 100%;
      border-radius: 0.75rem;
      border: 1px solid #e5e7eb;
      background: #f9fafb;
      padding: 0.625rem 1rem;
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
  <div class="w-full max-w-5xl py-8">
    <div class="overflow-hidden rounded-3xl shadow-2xl grid lg:grid-cols-[11fr_9fr]">

      {{-- ─── Left Panel ─────────────────────────────── --}}
      <div class="panel-bg relative hidden lg:flex flex-col justify-between p-10 text-white">
        <div class="flex items-center gap-3">
          <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl border border-white/20 bg-white/10">
            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
          </div>
          <div>
            <p class="font-bold text-sm leading-none">PUI Admin</p>
            <p class="mt-0.5 text-[10px] font-medium tracking-widest uppercase text-indigo-300">Attendance Security</p>
          </div>
        </div>

        <div>
          <h1 class="text-[2.4rem] font-bold leading-tight">Buat Akun<br>Administrator.</h1>
          <p class="mt-4 max-w-xs text-sm leading-relaxed text-indigo-200">Form ini hanya tersedia sebelum ada akun administrator terdaftar di sistem.</p>

          <div class="mt-8 rounded-2xl border border-white/10 bg-white/5 p-5">
            <p class="text-sm font-semibold text-white">Perhatian</p>
            <p class="mt-1.5 text-xs leading-relaxed text-indigo-300">Setelah akun administrator pertama dibuat, halaman registrasi ini tidak dapat diakses lagi. Simpan kredensial Anda dengan aman.</p>
          </div>
        </div>

        <p class="text-[11px] text-indigo-400/70">© {{ date('Y') }} PUI System. Hak cipta dilindungi.</p>
      </div>

      {{-- ─── Right Panel (Form) ──────────────────────── --}}
      <div class="flex flex-col justify-center bg-white p-8 sm:p-10">
        <div class="mb-6 flex items-center gap-2 lg:hidden">
          <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-white">
            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/><path d="m9 12 2 2 4-4"/></svg>
          </div>
          <span class="text-sm font-bold text-gray-900">PUI Admin</span>
        </div>

        <div class="mb-6">
          <h2 class="text-2xl font-bold text-gray-900">Buat Akun Admin</h2>
          <p class="mt-1.5 text-sm text-gray-500">Lengkapi data untuk membuat akun administrator pertama.</p>
        </div>

        @if ($errors->any())
          <div class="mb-5 flex items-start gap-3 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            <svg class="mt-0.5 h-4 w-4 shrink-0 text-rose-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span>{{ $errors->first() }}</span>
          </div>
        @endif

        <form method="POST" action="{{ route('dashboard.register.submit') }}" class="space-y-4">
          @csrf

          <div>
            <label for="nama" class="mb-1.5 block text-sm font-medium text-gray-700">Nama Lengkap</label>
            <input id="nama" name="nama" type="text" value="{{ old('nama') }}" required
              placeholder="Nama Administrator" class="input-field">
          </div>

          <div>
            <label for="email" class="mb-1.5 block text-sm font-medium text-gray-700">Alamat Email</label>
            <input id="email" name="email" type="email" value="{{ old('email') }}" required
              placeholder="admin@perusahaan.com" class="input-field">
          </div>

          <div class="grid gap-4 sm:grid-cols-2">
            <div>
              <label for="password" class="mb-1.5 block text-sm font-medium text-gray-700">Password</label>
              <input id="password" name="password" type="password" required
                placeholder="Min. 8 karakter" class="input-field">
            </div>
            <div>
              <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-gray-700">Konfirmasi Password</label>
              <input id="password_confirmation" name="password_confirmation" type="password" required
                placeholder="Ulangi password" class="input-field">
            </div>
          </div>

          <div class="pt-1">
            <button type="submit"
              class="w-full rounded-xl bg-indigo-600 px-4 py-3 text-sm font-semibold text-white shadow-sm transition-all hover:bg-indigo-700 hover:shadow-md active:scale-[0.99]">
              Buat Akun Administrator
            </button>
          </div>
        </form>

        <p class="mt-6 text-center text-sm text-gray-500">
          Sudah punya akun?
          <a href="{{ route('dashboard.login.form') }}" class="font-semibold text-indigo-600 hover:text-indigo-700 hover:underline underline-offset-2">Masuk di sini</a>
        </p>
      </div>
    </div>
  </div>
</body>
</html>
