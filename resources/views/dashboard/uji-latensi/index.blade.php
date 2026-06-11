@extends('dashboard.layouts.app', ['title' => 'Uji Kuantitatif'])

@section('content')

{{-- ─── Header ─────────────────────────────────────────────── --}}
<div class="mb-4">
  <h2 class="text-xl font-bold text-gray-900">Uji Kuantitatif — Tabel 4.23–4.27</h2>
  <p class="mt-0.5 text-sm text-gray-400">Pengujian latensi presensi, akurasi geofence, deteksi mock location, dan offline queue.</p>
</div>

{{-- ─── Info: tidak ada data tersimpan ─────────────────────── --}}
<div class="mb-4 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3">
  <div class="flex items-center gap-2 text-sm text-emerald-700">
    <i data-lucide="shield-check" class="h-4 w-4 shrink-0 text-emerald-600"></i>
    <span><strong>Data uji tidak disimpan ke database.</strong>
      Setiap request dijalankan di dalam transaksi yang selalu di-rollback — latensi terukur akurat, database tetap bersih.</span>
  </div>
  <button id="btn-bersihkan" class="shrink-0 rounded-xl border border-emerald-300 bg-white px-3 py-1.5 text-xs font-semibold text-emerald-700 hover:bg-emerald-100">
    Hapus data uji lama (jika ada)
  </button>
</div>
<div id="db-clean-msg" class="mb-3 hidden rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2.5 text-xs font-semibold text-emerald-700"></div>

{{-- ─── Tab Navigation ─────────────────────────────────────── --}}
<div class="flex overflow-x-auto rounded-t-2xl border border-b-0 border-gray-200 bg-white">
  <button class="tab-btn px-5 py-3.5 text-sm font-semibold whitespace-nowrap border-b-2 border-indigo-500 text-indigo-700" data-tab="latensi">
    Latensi Presensi <span class="ml-1 font-normal text-gray-400 text-xs">4.23–4.24</span>
  </button>
  <button class="tab-btn px-5 py-3.5 text-sm font-semibold whitespace-nowrap border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="geofence">
    Akurasi Geofence <span class="ml-1 font-normal text-gray-400 text-xs">4.25</span>
  </button>
  <button class="tab-btn px-5 py-3.5 text-sm font-semibold whitespace-nowrap border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="mock">
    Deteksi Mock Location <span class="ml-1 font-normal text-gray-400 text-xs">4.26</span>
  </button>
  <button class="tab-btn px-5 py-3.5 text-sm font-semibold whitespace-nowrap border-b-2 border-transparent text-gray-500 hover:text-gray-700" data-tab="offline">
    Offline Queue <span class="ml-1 font-normal text-gray-400 text-xs">4.27</span>
  </button>
</div>

{{-- ════════════════════════════════════════════════════════════
     TAB 1 — Latensi Presensi (Tabel 4.23 & 4.24)
     ════════════════════════════════════════════════════════════ --}}
<div id="panel-latensi" class="tab-panel rounded-b-2xl rounded-tr-2xl border border-gray-200 p-5 space-y-5">

  {{-- Kontrol --}}
  <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
    <div class="flex flex-wrap items-end gap-4">
      <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700">Jumlah Iterasi <span class="text-gray-400">(maks 30)</span></label>
        <input id="L-n" type="number" min="1" max="30" value="10"
          class="w-28 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
      </div>
      <button id="L-run" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50">
        <i data-lucide="play" class="h-4 w-4"></i> Jalankan Uji
      </button>
      <button id="L-reset" class="hidden rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-500 hover:bg-gray-50">Reset</button>
    </div>
    <p class="mt-2 text-xs text-gray-400">Setiap iterasi = 1 akun test baru. Total = N×2 request (masuk + pulang).</p>
    <div id="L-prog-wrap" class="mt-4 hidden">
      <div class="mb-1 flex justify-between text-xs text-gray-500"><span id="L-prog-lbl">Memulai...</span><span id="L-prog-pct">0%</span></div>
      <div class="h-2 w-full rounded-full bg-gray-100"><div id="L-prog-bar" class="h-2 rounded-full bg-indigo-500 transition-all duration-300" style="width:0%"></div></div>
    </div>
    <div id="L-err" class="mt-3 hidden rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm text-rose-700"></div>
  </div>

  {{-- Hasil --}}
  <div id="L-results" class="hidden space-y-5">
    {{-- Info --}}
    <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
      <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm"><p class="text-xs text-gray-400">Timestamp</p><p id="L-ts" class="mt-1 font-mono text-xs font-semibold text-gray-800"></p></div>
      <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm"><p class="text-xs text-gray-400">Geofencing</p><p id="L-geo" class="mt-1 text-sm font-semibold text-gray-800"></p></div>
      <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm"><p class="text-xs text-gray-400">Request Berhasil</p><p id="L-req" class="mt-1 text-sm font-semibold text-gray-800"></p></div>
      <div class="rounded-xl border border-gray-100 bg-white p-4 shadow-sm"><p class="text-xs text-gray-400">Endpoint</p><p class="mt-1 font-mono text-xs font-semibold text-gray-800">POST /api/presensi</p></div>
    </div>

    {{-- Tabel statistik (Tabel 4.23 & 4.24) --}}
    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
      <div class="border-b border-gray-100 px-5 py-3.5">
        <h3 class="font-semibold text-gray-800">Tabel 4.23 & 4.24 — Statistik Latensi (ms)</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead><tr class="border-b border-gray-100 bg-gray-50/70">
            <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-400">Metrik</th>
            <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wider text-indigo-400">Presensi Masuk (ms)</th>
            <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wider text-emerald-500">Presensi Pulang (ms)</th>
          </tr></thead>
          <tbody id="L-stats-tbody" class="divide-y divide-gray-50"></tbody>
        </table>
      </div>
    </div>

    {{-- Charts --}}
    <div class="grid gap-5 lg:grid-cols-2">
      <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <h3 class="mb-1 font-semibold text-gray-800">Latensi per Iterasi</h3>
        <p class="mb-3 text-xs text-gray-400">Setiap titik = 1 request</p>
        <div class="relative h-60"><canvas id="L-line-chart"></canvas></div>
      </div>
      <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <h3 class="mb-1 font-semibold text-gray-800">Perbandingan Statistik</h3>
        <p class="mb-3 text-xs text-gray-400">Indigo = Masuk | Hijau = Pulang</p>
        <div class="relative h-60"><canvas id="L-bar-chart"></canvas></div>
      </div>
    </div>
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
      <h3 class="mb-1 font-semibold text-gray-800">Histogram Distribusi Latensi</h3>
      <p class="mb-3 text-xs text-gray-400">Frekuensi per bucket 50 ms</p>
      <div class="relative h-48"><canvas id="L-hist-chart"></canvas></div>
    </div>

    {{-- Raw data --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
      <h3 class="mb-3 font-semibold text-gray-800">Data Mentah</h3>
      <div class="space-y-2 text-xs">
        <div><span class="font-semibold text-indigo-600">Masuk:</span> <span id="L-raw-masuk" class="ml-1 font-mono text-gray-600 break-all"></span></div>
        <div><span class="font-semibold text-emerald-600">Pulang:</span> <span id="L-raw-pulang" class="ml-1 font-mono text-gray-600 break-all"></span></div>
      </div>
    </div>
  </div>

  {{-- ── Data Nyata (dari presensi_latency.log) ─────────────── --}}
  <div class="mt-6 border-t border-dashed border-amber-300 pt-5 space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h3 class="font-semibold text-amber-800">Data Nyata — Tabel 4.23 & 4.24</h3>
        <p class="text-xs text-amber-600">Dibaca dari <code class="rounded bg-amber-100 px-1 font-mono">presensi_latency.log</code> — mencatat setiap request presensi nyata dari mobile.</p>
      </div>
      <button id="LR-load" class="inline-flex items-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-amber-600">
        <i data-lucide="database" class="h-4 w-4"></i> Muat Data Nyata
      </button>
    </div>
    <div id="LR-err" class="hidden rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm text-rose-700"></div>
    <div id="LR-results" class="hidden space-y-4">
      <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
        <div class="rounded-xl border border-amber-100 bg-amber-50 p-3"><p class="text-xs text-amber-500">Total entri log</p><p id="LR-total" class="mt-1 text-lg font-bold text-amber-800"></p></div>
        <div class="rounded-xl border border-amber-100 bg-amber-50 p-3"><p class="text-xs text-amber-500">Masuk berhasil</p><p id="LR-masuk-n" class="mt-1 text-lg font-bold text-amber-800"></p></div>
        <div class="rounded-xl border border-amber-100 bg-amber-50 p-3"><p class="text-xs text-amber-500">Pulang berhasil</p><p id="LR-pulang-n" class="mt-1 text-lg font-bold text-amber-800"></p></div>
        <div class="rounded-xl border border-amber-100 bg-amber-50 p-3"><p class="text-xs text-amber-500">Total gagal</p><p id="LR-gagal" class="mt-1 text-lg font-bold text-rose-600"></p></div>
      </div>
      <div class="overflow-hidden rounded-2xl border border-amber-100 bg-white shadow-sm">
        <div class="border-b border-amber-100 bg-amber-50/50 px-5 py-3"><h4 class="font-semibold text-amber-800">Statistik Latensi Nyata (ms)</h4></div>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead><tr class="border-b border-amber-100 bg-amber-50/30">
              <th class="px-5 py-3 text-left text-xs font-bold uppercase text-amber-400">Metrik</th>
              <th class="px-5 py-3 text-right text-xs font-bold uppercase text-amber-500">Masuk (ms)</th>
              <th class="px-5 py-3 text-right text-xs font-bold uppercase text-amber-600">Pulang (ms)</th>
            </tr></thead>
            <tbody id="LR-tbody" class="divide-y divide-amber-50"></tbody>
          </table>
        </div>
      </div>
      <div class="grid gap-4 lg:grid-cols-2">
        <div class="rounded-2xl border border-amber-100 bg-white p-4 shadow-sm">
          <h4 class="mb-3 font-semibold text-amber-800">Latensi per Request (nyata)</h4>
          <div class="relative h-56"><canvas id="LR-line-chart"></canvas></div>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-white p-4 shadow-sm">
          <h4 class="mb-3 font-semibold text-amber-800">Perbandingan Statistik (nyata)</h4>
          <div class="relative h-56"><canvas id="LR-bar-chart"></canvas></div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ════════════════════════════════════════════════════════════
     TAB 2 — Akurasi Geofence (Tabel 4.25)
     ════════════════════════════════════════════════════════════ --}}
<div id="panel-geofence" class="tab-panel hidden rounded-b-2xl rounded-tr-2xl border border-gray-200 p-5 space-y-5">

  <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
    <div class="flex flex-wrap items-end gap-4">
      <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700">Jumlah Sampel per Skenario <span class="text-gray-400">(maks 30)</span></label>
        <input id="G-n" type="number" min="1" max="30" value="10"
          class="w-28 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
      </div>
      <button id="G-run" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50">
        <i data-lucide="play" class="h-4 w-4"></i> Jalankan Uji
      </button>
      <button id="G-reset" class="hidden rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-500 hover:bg-gray-50">Reset</button>
    </div>
    <p class="mt-2 text-xs text-gray-400">Fase 1: N request di <strong>luar</strong> radius (harus ditolak). Fase 2: N request di <strong>dalam</strong> radius (harus diterima). Total = N×2 + N akun.</p>
    <div id="G-prog-wrap" class="mt-4 hidden">
      <div class="mb-1 flex justify-between text-xs text-gray-500"><span id="G-prog-lbl">Memulai...</span><span id="G-prog-pct">0%</span></div>
      <div class="h-2 w-full rounded-full bg-gray-100"><div id="G-prog-bar" class="h-2 rounded-full bg-emerald-500 transition-all duration-300" style="width:0%"></div></div>
    </div>
    <div id="G-err" class="mt-3 hidden rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm text-rose-700"></div>
  </div>

  <div id="G-results" class="hidden space-y-5">
    {{-- Tabel 4.25 --}}
    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
      <div class="border-b border-gray-100 px-5 py-3.5">
        <h3 class="font-semibold text-gray-800">Tabel 4.25 — Uji Akurasi Geofence</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead><tr class="border-b border-gray-100 bg-gray-50/70">
            <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-400">No</th>
            <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-400">Skenario</th>
            <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wider text-gray-400">Sampel</th>
            <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wider text-emerald-500">Benar</th>
            <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wider text-rose-400">Salah</th>
            <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wider text-indigo-400">Akurasi (%)</th>
            <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wider text-gray-400">Avg Latency (ms)</th>
          </tr></thead>
          <tbody id="G-tbody" class="divide-y divide-gray-50"></tbody>
        </table>
      </div>
    </div>

    {{-- Charts --}}
    <div class="grid gap-5 lg:grid-cols-2">
      <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <h3 class="mb-1 font-semibold text-gray-800">Benar vs Salah per Skenario</h3>
        <p class="mb-3 text-xs text-gray-400">Hijau = sesuai harapan | Merah = tidak sesuai</p>
        <div class="relative h-56"><canvas id="G-bar-chart"></canvas></div>
      </div>
      <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <h3 class="mb-1 font-semibold text-gray-800">Latensi per Skenario</h3>
        <p class="mb-3 text-xs text-gray-400">Rata-rata latensi per request (ms)</p>
        <div class="relative h-56"><canvas id="G-lat-chart"></canvas></div>
      </div>
    </div>

    {{-- Distribusi latensi --}}
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
      <h3 class="mb-1 font-semibold text-gray-800">Distribusi Latensi — Luar vs Dalam Radius</h3>
      <p class="mb-3 text-xs text-gray-400">Histogram frekuensi per bucket 50 ms</p>
      <div class="relative h-48"><canvas id="G-hist-chart"></canvas></div>
    </div>
  </div>

  {{-- ── Data Nyata (dari DB + log) ──────────────────────────── --}}
  <div class="mt-6 border-t border-dashed border-amber-300 pt-5 space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h3 class="font-semibold text-amber-800">Data Nyata — Tabel 4.25</h3>
        <p class="text-xs text-amber-600">Presensi tersimpan = koordinat dalam radius. Ditolak 422 = dari <code class="rounded bg-amber-100 px-1 font-mono">presensi_latency.log</code>.</p>
      </div>
      <button id="GR-load" class="inline-flex items-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-amber-600">
        <i data-lucide="database" class="h-4 w-4"></i> Muat Data Nyata
      </button>
    </div>
    <div id="GR-err" class="hidden rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm text-rose-700"></div>
    <div id="GR-results" class="hidden space-y-4">
      <div class="overflow-hidden rounded-2xl border border-amber-100 bg-white shadow-sm">
        <div class="border-b border-amber-100 bg-amber-50/50 px-5 py-3"><h4 class="font-semibold text-amber-800">Tabel 4.25 — Akurasi Geofence (Data Nyata)</h4></div>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead><tr class="border-b border-amber-100 bg-amber-50/30">
              <th class="px-5 py-3 text-left text-xs font-bold uppercase text-amber-400">No</th>
              <th class="px-5 py-3 text-left text-xs font-bold uppercase text-amber-400">Skenario</th>
              <th class="px-5 py-3 text-right text-xs font-bold uppercase text-amber-400">Sampel</th>
              <th class="px-5 py-3 text-right text-xs font-bold uppercase text-emerald-500">Benar</th>
              <th class="px-5 py-3 text-right text-xs font-bold uppercase text-rose-400">Salah</th>
              <th class="px-5 py-3 text-right text-xs font-bold uppercase text-amber-500">Akurasi (%)</th>
              <th class="px-5 py-3 text-left text-xs font-bold uppercase text-amber-400">Catatan</th>
            </tr></thead>
            <tbody id="GR-tbody" class="divide-y divide-amber-50"></tbody>
          </table>
        </div>
      </div>
      <div class="rounded-2xl border border-amber-100 bg-white p-4 shadow-sm">
        <h4 class="mb-1 font-semibold text-amber-800">Distribusi Jarak dari Pusat Geofence</h4>
        <p class="mb-3 text-xs text-amber-600">Hanya presensi yang lolos (dalam radius). Sumbu X = jarak (meter).</p>
        <div class="relative h-48"><canvas id="GR-chart"></canvas></div>
      </div>
    </div>
  </div>
</div>

{{-- ════════════════════════════════════════════════════════════
     TAB 3 — Deteksi Mock Location (Tabel 4.26)
     ════════════════════════════════════════════════════════════ --}}
<div id="panel-mock" class="tab-panel hidden rounded-b-2xl rounded-tr-2xl border border-gray-200 p-5 space-y-5">

  <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
    <div class="flex flex-wrap items-end gap-4">
      <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700">Sampel per Skenario <span class="text-gray-400">(maks 30)</span></label>
        <input id="M-n" type="number" min="1" max="30" value="10"
          class="w-28 rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm text-gray-900 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20">
      </div>
      <button id="M-run" class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 disabled:opacity-50">
        <i data-lucide="play" class="h-4 w-4"></i> Jalankan Uji
      </button>
      <button id="M-reset" class="hidden rounded-xl border border-gray-200 px-4 py-2.5 text-sm font-medium text-gray-500 hover:bg-gray-50">Reset</button>
    </div>
    <p class="mt-2 text-xs text-gray-400">7 skenario × N sampel = N×7 request ke <code class="rounded bg-gray-100 px-1 font-mono">POST /api/log-deteksi</code>. Akun bisa dipakai ulang antar skenario.</p>
    <div id="M-prog-wrap" class="mt-4 hidden">
      <div class="mb-1 flex justify-between text-xs text-gray-500"><span id="M-prog-lbl">Memulai...</span><span id="M-prog-pct">0%</span></div>
      <div class="h-2 w-full rounded-full bg-gray-100"><div id="M-prog-bar" class="h-2 rounded-full bg-amber-500 transition-all duration-300" style="width:0%"></div></div>
    </div>
    <div id="M-err" class="mt-3 hidden rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm text-rose-700"></div>
  </div>

  <div id="M-results" class="hidden space-y-5">
    {{-- Tabel 4.26 --}}
    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
      <div class="border-b border-gray-100 px-5 py-3.5">
        <h3 class="font-semibold text-gray-800">Tabel 4.26 — Uji Deteksi Mock Location (Tahapan Sekuensial + Fail-Closed)</h3>
      </div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead><tr class="border-b border-gray-100 bg-gray-50/70">
            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-400">No</th>
            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-400">Tahap</th>
            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-400">Skenario</th>
            <th class="px-4 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-400">Expected Status</th>
            <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-gray-400">Sampel</th>
            <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-emerald-500">Sesuai</th>
            <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-rose-400">Tdk Sesuai</th>
            <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-indigo-400">Akurasi (%)</th>
            <th class="px-4 py-3 text-right text-xs font-bold uppercase tracking-wider text-gray-400">Avg Lat. (ms)</th>
          </tr></thead>
          <tbody id="M-tbody" class="divide-y divide-gray-50"></tbody>
        </table>
      </div>
    </div>

    {{-- Charts --}}
    <div class="grid gap-5 lg:grid-cols-2">
      <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <h3 class="mb-1 font-semibold text-gray-800">Akurasi per Skenario (%)</h3>
        <p class="mb-3 text-xs text-gray-400">100% = semua request berhasil disimpan server</p>
        <div class="relative h-60"><canvas id="M-acc-chart"></canvas></div>
      </div>
      <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
        <h3 class="mb-1 font-semibold text-gray-800">Rata-rata Latensi per Skenario (ms)</h3>
        <p class="mb-3 text-xs text-gray-400">Latensi endpoint POST /api/log-deteksi</p>
        <div class="relative h-60"><canvas id="M-lat-chart"></canvas></div>
      </div>
    </div>

    {{-- Statistik latensi gabungan --}}
    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
      <div class="border-b border-gray-100 px-5 py-3.5"><h3 class="font-semibold text-gray-800">Statistik Latensi Gabungan (semua skenario)</h3></div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead><tr class="border-b border-gray-100 bg-gray-50/70">
            <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-400">Metrik</th>
            <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wider text-amber-500">Nilai (ms)</th>
          </tr></thead>
          <tbody id="M-stat-tbody" class="divide-y divide-gray-50"></tbody>
        </table>
      </div>
    </div>
  </div>

  {{-- ── Data Nyata (dari log_deteksi) ───────────────────────── --}}
  <div class="mt-6 border-t border-dashed border-amber-300 pt-5 space-y-4">
    <div class="flex flex-wrap items-center justify-between gap-3">
      <div>
        <h3 class="font-semibold text-amber-800">Data Nyata — Tabel 4.26</h3>
        <p class="text-xs text-amber-600">Dibaca dari tabel <code class="rounded bg-amber-100 px-1 font-mono">log_deteksi</code> — setiap presensi mobile mengirim sinyal keamanan yang disimpan di sini.</p>
      </div>
      <button id="MR-load" class="inline-flex items-center gap-2 rounded-xl bg-amber-500 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-amber-600">
        <i data-lucide="database" class="h-4 w-4"></i> Muat Data Nyata
      </button>
    </div>
    <div id="MR-err" class="hidden rounded-xl border border-rose-200 bg-rose-50 px-4 py-2.5 text-sm text-rose-700"></div>
    <div id="MR-results" class="hidden space-y-4">
      <div class="overflow-hidden rounded-2xl border border-amber-100 bg-white shadow-sm">
        <div class="border-b border-amber-100 bg-amber-50/50 px-5 py-3 flex items-center justify-between">
          <h4 class="font-semibold text-amber-800">Tabel 4.26 — Deteksi Mock Location (Data Nyata)</h4>
          <span class="text-xs text-amber-500">Total log: <span id="MR-total" class="font-bold"></span></span>
        </div>
        <div class="overflow-x-auto">
          <table class="min-w-full text-sm">
            <thead><tr class="border-b border-amber-100 bg-amber-50/30">
              <th class="px-4 py-3 text-left text-xs font-bold uppercase text-amber-400">No</th>
              <th class="px-4 py-3 text-left text-xs font-bold uppercase text-amber-400">Tahap</th>
              <th class="px-4 py-3 text-left text-xs font-bold uppercase text-amber-400">Skenario</th>
              <th class="px-4 py-3 text-left text-xs font-bold uppercase text-amber-400">Expected</th>
              <th class="px-4 py-3 text-right text-xs font-bold uppercase text-amber-400">Sampel</th>
              <th class="px-4 py-3 text-right text-xs font-bold uppercase text-emerald-500">Sesuai</th>
              <th class="px-4 py-3 text-right text-xs font-bold uppercase text-rose-400">Tdk Sesuai</th>
              <th class="px-4 py-3 text-right text-xs font-bold uppercase text-amber-500">Akurasi (%)</th>
            </tr></thead>
            <tbody id="MR-tbody" class="divide-y divide-amber-50"></tbody>
          </table>
        </div>
      </div>
      <div class="grid gap-4 lg:grid-cols-2">
        <div class="rounded-2xl border border-amber-100 bg-white p-4 shadow-sm">
          <h4 class="mb-3 font-semibold text-amber-800">Akurasi per Tahap — Data Nyata (%)</h4>
          <div class="relative h-56"><canvas id="MR-acc-chart"></canvas></div>
        </div>
        <div class="rounded-2xl border border-amber-100 bg-white p-4 shadow-sm">
          <h4 class="mb-3 font-semibold text-amber-800">Distribusi Final Status</h4>
          <div class="relative h-56"><canvas id="MR-dist-chart"></canvas></div>
        </div>
      </div>
    </div>
  </div>
</div>

{{-- ════════════════════════════════════════════════════════════
     TAB 4 — Offline Queue (Tabel 4.27)
     ════════════════════════════════════════════════════════════ --}}
<div id="panel-offline" class="tab-panel hidden rounded-b-2xl rounded-tr-2xl border border-gray-200 p-5 space-y-5">

  <div class="rounded-xl border border-amber-200 bg-amber-50 p-4 text-sm text-amber-700">
    <p class="font-semibold">Pengujian dilakukan secara manual pada perangkat Android.</p>
    <p class="mt-1 text-xs">Uji offline queue tidak dapat diotomasi dari web karena membutuhkan kondisi jaringan offline nyata pada perangkat mobile. Masukkan hasil pengujian manual di bawah ini.</p>
  </div>

  {{-- Input manual --}}
  <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
    <h3 class="mb-4 font-semibold text-gray-800">Input Hasil Pengujian Manual</h3>
    <div class="grid gap-4 sm:grid-cols-3">
      <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700">Total Sampel</label>
        <input id="O-total" type="number" min="1" value="30" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:border-indigo-500">
      </div>
      <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700">Terkirim (berhasil)</label>
        <input id="O-terkirim" type="number" min="0" value="0" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:border-indigo-500">
      </div>
      <div>
        <label class="mb-1.5 block text-sm font-medium text-gray-700">Keterangan</label>
        <input id="O-ket" type="text" placeholder="cth: Jaringan Wi-Fi stabil" class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3 py-2 text-sm outline-none focus:border-indigo-500">
      </div>
    </div>
    <button id="O-hitung" class="mt-4 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
      Hitung & Tampilkan
    </button>
  </div>

  {{-- Tabel 4.27 --}}
  <div id="O-results" class="hidden space-y-5">
    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
      <div class="border-b border-gray-100 px-5 py-3.5"><h3 class="font-semibold text-gray-800">Tabel 4.27 — Uji Offline Queue</h3></div>
      <div class="overflow-x-auto">
        <table class="min-w-full text-sm">
          <thead><tr class="border-b border-gray-100 bg-gray-50/70">
            <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-400">No</th>
            <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-400">Skenario</th>
            <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wider text-gray-400">Sampel</th>
            <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wider text-emerald-500">Terkirim</th>
            <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wider text-rose-400">Gagal</th>
            <th class="px-5 py-3 text-right text-xs font-bold uppercase tracking-wider text-indigo-400">Keberhasilan (%)</th>
            <th class="px-5 py-3 text-left text-xs font-bold uppercase tracking-wider text-gray-400">Keterangan</th>
          </tr></thead>
          <tbody id="O-tbody" class="divide-y divide-gray-50"></tbody>
        </table>
      </div>
    </div>
    <div class="rounded-2xl border border-gray-100 bg-white p-5 shadow-sm">
      <h3 class="mb-3 font-semibold text-gray-800">Visualisasi Keberhasilan Offline Queue</h3>
      <div class="relative h-48"><canvas id="O-chart"></canvas></div>
    </div>
  </div>
</div>

@endsection

@push('head')
  <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
@endpush

@push('scripts')
<script>
'use strict';

// ─────────────────────────────────────────────────────────────
// Shared utilities
// ─────────────────────────────────────────────────────────────
const TODAY  = new Date().toISOString().slice(0, 10);
const CSRF   = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

// Panggil proxy endpoint (bukan API publik) — response sudah include latency_ms dari server
async function proxyPost(route, body) {
  const resp = await fetch(route, {
    method : 'POST',
    headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
    body   : JSON.stringify(body),
  });
  return resp.json().catch(() => ({ ok: false, latency_ms: 0, status_code: 0 }));
}

async function getGeo() {
  const resp = await fetch('{{ route('dashboard.uji-latensi.geofencing') }}', { headers: { 'Accept': 'application/json' } });
  const d = await resp.json().catch(() => null);
  return d?.data?.[0] ?? null;
}

function calcStats(arr) {
  if (!arr.length) return null;
  const s = [...arr].sort((a, b) => a - b);
  const n = s.length;
  const mean = s.reduce((a, b) => a + b, 0) / n;
  const variance = s.reduce((acc, v) => acc + (v - mean) ** 2, 0) / Math.max(n - 1, 1);
  const median = n % 2 === 0 ? (s[n / 2 - 1] + s[n / 2]) / 2 : s[Math.floor(n / 2)];
  const pct = p => s[Math.min(Math.floor(n * p), n - 1)];
  return {
    n, min: s[0], max: s[n - 1],
    mean: Math.round(mean * 100) / 100,
    median, stdev: Math.round(Math.sqrt(variance) * 100) / 100,
    p90: pct(0.90), p95: pct(0.95), p99: pct(0.99),
  };
}

// Hapus data uji lama (dari sesi sebelumnya yang masih pakai cara lama)
document.getElementById('btn-bersihkan').addEventListener('click', async () => {
  if (!confirm('Hapus data uji lama (akun *@test.local) dari database?')) return;
  const btn = document.getElementById('btn-bersihkan');
  btn.disabled = true; btn.textContent = 'Menghapus...';
  try {
    const r = await fetch('{{ route('dashboard.uji-latensi.bersihkan') }}', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
    });
    const d = await r.json();
    const msg = document.getElementById('db-clean-msg');
    msg.textContent = `✓ ${d.pesan}${d.dihapus?.pengguna ? ` — ${d.dihapus.pengguna} akun dihapus` : ''}`;
    msg.classList.remove('hidden');
  } catch (e) { alert('Gagal: ' + e.message); }
  finally { btn.disabled = false; btn.textContent = 'Hapus data uji lama (jika ada)'; }
});

function setProgress(barId, lblId, pctId, step, total, label) {
  const p = Math.round((step / total) * 100);
  document.getElementById(barId).style.width = p + '%';
  document.getElementById(lblId).textContent = label;
  document.getElementById(pctId).textContent = p + '%';
}

function mkChart(id, config) {
  const el = document.getElementById(id);
  return new Chart(el, config);
}

const CHART_DEFAULTS = {
  responsive: true, maintainAspectRatio: false,
  plugins: { legend: { display: true, position: 'top', labels: { boxWidth: 12, font: { size: 11 } } } },
};

function makeHistBuckets(arrays, bucketSize = 50) {
  const all = arrays.flat();
  if (!all.length) return { labels: [], datasets: [] };
  const minV = Math.min(...all);
  const maxV = Math.max(...all);
  const start = Math.floor(minV / bucketSize) * bucketSize;
  const end = Math.ceil(maxV / bucketSize) * bucketSize;
  const buckets = [];
  for (let lo = start; lo < end; lo += bucketSize) buckets.push(lo);
  const labels = buckets.map(lo => `${lo}–${lo + bucketSize}`);
  const countInto = (arr, lo) => arr.filter(v => v >= lo && v < lo + bucketSize).length;
  return { buckets, labels, countInto };
}

// ─────────────────────────────────────────────────────────────
// Tab switching
// ─────────────────────────────────────────────────────────────
document.querySelectorAll('.tab-btn').forEach(btn => {
  btn.addEventListener('click', () => {
    document.querySelectorAll('.tab-btn').forEach(b => {
      b.classList.remove('border-indigo-500', 'text-indigo-700');
      b.classList.add('border-transparent', 'text-gray-500');
    });
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.add('hidden'));
    btn.classList.add('border-indigo-500', 'text-indigo-700');
    btn.classList.remove('border-transparent', 'text-gray-500');
    document.getElementById('panel-' + btn.dataset.tab).classList.remove('hidden');
    lucide.createIcons();
  });
});

// ─────────────────────────────────────────────────────────────
// TAB 1 — LATENSI PRESENSI
// ─────────────────────────────────────────────────────────────
let lCharts = {};

function lSetProg(step, total, label) {
  setProgress('L-prog-bar', 'L-prog-lbl', 'L-prog-pct', step, total, label);
}

function lRenderTable(sm, sp) {
  const ROWS = [
    ['N (berhasil)', 'n', ''], ['Minimum', 'min', ' ms'], ['Maksimum', 'max', ' ms'],
    ['Rata-rata (mean)', 'mean', ' ms'], ['Median (P50)', 'median', ' ms'],
    ['Std. Deviasi', 'stdev', ' ms'], ['P90', 'p90', ' ms'], ['P95', 'p95', ' ms'], ['P99', 'p99', ' ms'],
  ];
  document.getElementById('L-stats-tbody').innerHTML = ROWS.map(([label, key, unit]) => {
    const vm = sm?.[key] ?? '-'; const vp = sp?.[key] ?? '-';
    const bold = key === 'mean' ? 'font-bold' : '';
    const fmt = v => v === '-' ? '-' : v + unit;
    return `<tr class="hover:bg-slate-50/60">
      <td class="px-5 py-2.5 text-gray-600">${label}</td>
      <td class="px-5 py-2.5 text-right ${bold} text-indigo-700">${fmt(vm)}</td>
      <td class="px-5 py-2.5 text-right ${bold} text-emerald-700">${fmt(vp)}</td>
    </tr>`;
  }).join('');
}

function lRenderCharts(masuk, pulang) {
  Object.values(lCharts).forEach(c => c?.destroy());
  const labels = Array.from({ length: Math.max(masuk.length, pulang.length) }, (_, i) => `#${i + 1}`);
  lCharts.line = mkChart('L-line-chart', {
    type: 'line',
    data: { labels, datasets: [
      { label: 'Masuk (ms)', data: masuk, borderColor: '#6366f1', backgroundColor: 'rgba(99,102,241,0.07)', fill: true, tension: 0.35, pointRadius: 3 },
      { label: 'Pulang (ms)', data: pulang, borderColor: '#10b981', backgroundColor: 'rgba(16,185,129,0.07)', fill: true, tension: 0.35, pointRadius: 3 },
    ]},
    options: { ...CHART_DEFAULTS, scales: { y: { beginAtZero: true, ticks: { callback: v => v + 'ms' }, grid: { color: '#f3f4f6' } }, x: { grid: { display: false } } }, interaction: { intersect: false, mode: 'index' } },
  });

  const sm = calcStats(masuk); const sp = calcStats(pulang);
  const sKeys = ['min', 'mean', 'median', 'p90', 'p95', 'max'];
  lCharts.bar = mkChart('L-bar-chart', {
    type: 'bar',
    data: { labels: ['Min', 'Mean', 'Median', 'P90', 'P95', 'Max'], datasets: [
      { label: 'Masuk (ms)', data: sKeys.map(k => sm?.[k] ?? 0), backgroundColor: 'rgba(99,102,241,0.75)', borderRadius: 5 },
      { label: 'Pulang (ms)', data: sKeys.map(k => sp?.[k] ?? 0), backgroundColor: 'rgba(16,185,129,0.75)', borderRadius: 5 },
    ]},
    options: { ...CHART_DEFAULTS, scales: { y: { beginAtZero: true, ticks: { callback: v => v + 'ms' }, grid: { color: '#f3f4f6' } }, x: { grid: { display: false } } } },
  });

  const { labels: hLabels, buckets, countInto } = makeHistBuckets([masuk, pulang]);
  lCharts.hist = mkChart('L-hist-chart', {
    type: 'bar',
    data: { labels: hLabels, datasets: [
      { label: 'Masuk', data: buckets.map(lo => countInto(masuk, lo)), backgroundColor: 'rgba(99,102,241,0.65)', borderRadius: 4 },
      { label: 'Pulang', data: buckets.map(lo => countInto(pulang, lo)), backgroundColor: 'rgba(16,185,129,0.65)', borderRadius: 4 },
    ]},
    options: { ...CHART_DEFAULTS, scales: {
      x: { grid: { display: false }, title: { display: true, text: 'Rentang latensi (ms)', color: '#9ca3af', font: { size: 11 } } },
      y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f3f4f6' }, title: { display: true, text: 'Frekuensi', color: '#9ca3af', font: { size: 11 } } },
    }},
  });
}

document.getElementById('L-run').addEventListener('click', async () => {
  const N     = Math.max(1, Math.min(30, parseInt(document.getElementById('L-n').value) || 10));
  const total = 1 + N + N;
  const btn   = document.getElementById('L-run');
  btn.disabled = true;
  document.getElementById('L-reset').classList.add('hidden');
  document.getElementById('L-results').classList.add('hidden');
  document.getElementById('L-prog-wrap').classList.remove('hidden');
  document.getElementById('L-err').classList.add('hidden');

  let step = 0;
  const masuk = [], pulang = [];

  try {
    lSetProg(++step, total, 'Mengambil geofencing aktif...');
    const geo = await getGeo();
    if (!geo) { document.getElementById('L-err').textContent = 'Tidak ada geofencing aktif.'; document.getElementById('L-err').classList.remove('hidden'); return; }

    for (let i = 1; i <= N; i++) {
      lSetProg(++step, total, `Presensi MASUK ${i}/${N}...`);
      const d = await proxyPost('{{ route('dashboard.uji-latensi.presensi') }}', {
        id_geofencing: geo.id, tipe_presensi: 'masuk',
        waktu: `${TODAY}T08:00:00`,
        latitude: parseFloat(geo.latitude), longitude: parseFloat(geo.longitude), status_presensi: 'hadir',
      });
      if (d.ok) masuk.push(d.latency_ms);
    }
    for (let i = 1; i <= N; i++) {
      lSetProg(++step, total, `Presensi PULANG ${i}/${N}...`);
      const d = await proxyPost('{{ route('dashboard.uji-latensi.presensi') }}', {
        id_geofencing: geo.id, tipe_presensi: 'pulang',
        waktu: `${TODAY}T17:00:00`,
        latitude: parseFloat(geo.latitude), longitude: parseFloat(geo.longitude), status_presensi: 'hadir',
      });
      if (d.ok) pulang.push(d.latency_ms);
    }

    lSetProg(total, total, 'Selesai!');
    document.getElementById('L-results').classList.remove('hidden');
    document.getElementById('L-ts').textContent  = new Date().toLocaleString('id-ID');
    document.getElementById('L-geo').textContent = `${geo.nama_lokasi} (r=${geo.radius_meter}m)`;
    document.getElementById('L-req').textContent = `Masuk: ${masuk.length} | Pulang: ${pulang.length}`;
    document.getElementById('L-raw-masuk').textContent  = '[' + masuk.join(', ')  + ']';
    document.getElementById('L-raw-pulang').textContent = '[' + pulang.join(', ') + ']';
    lRenderTable(calcStats(masuk), calcStats(pulang));
    lRenderCharts(masuk, pulang);
    document.getElementById('L-results').scrollIntoView({ behavior: 'smooth', block: 'start' });
  } finally {
    btn.disabled = false;
    document.getElementById('L-reset').classList.remove('hidden');
    lucide.createIcons();
  }
});

document.getElementById('L-reset').addEventListener('click', () => {
  document.getElementById('L-results').classList.add('hidden');
  document.getElementById('L-prog-wrap').classList.add('hidden');
  document.getElementById('L-reset').classList.add('hidden');
  document.getElementById('L-prog-bar').style.width = '0%';
  Object.values(lCharts).forEach(c => c?.destroy()); lCharts = {};
});

// ─────────────────────────────────────────────────────────────
// TAB 2 — AKURASI GEOFENCE
// ─────────────────────────────────────────────────────────────
let gCharts = {};

function gSetProg(step, total, label) {
  setProgress('G-prog-bar', 'G-prog-lbl', 'G-prog-pct', step, total, label);
}

function gRenderResults(luarResult, dalamResult, geo) {
  // luarResult/dalamResult = { benar, salah, latencies }
  const scenarios = [
    { no: 1, label: 'Lokasi di luar radius', ...luarResult, expectedOutcome: 'Ditolak (HTTP 422)' },
    { no: 2, label: 'Lokasi di dalam radius', ...dalamResult, expectedOutcome: 'Diterima (HTTP 201)' },
  ];
  document.getElementById('G-tbody').innerHTML = scenarios.map(r => {
    const acc = r.benar + r.salah > 0 ? Math.round((r.benar / (r.benar + r.salah)) * 10000) / 100 : 0;
    const avgLat = r.latencies.length ? Math.round(r.latencies.reduce((a, b) => a + b, 0) / r.latencies.length) : '-';
    const accColor = acc === 100 ? 'text-emerald-700 font-bold' : acc >= 80 ? 'text-amber-600 font-semibold' : 'text-rose-600 font-bold';
    return `<tr class="hover:bg-slate-50/60">
      <td class="px-5 py-3 text-gray-500">${r.no}</td>
      <td class="px-5 py-3 font-medium text-gray-800">${r.label}</td>
      <td class="px-5 py-3 text-right text-gray-500">${r.benar + r.salah}</td>
      <td class="px-5 py-3 text-right font-semibold text-emerald-700">${r.benar}</td>
      <td class="px-5 py-3 text-right font-semibold text-rose-600">${r.salah}</td>
      <td class="px-5 py-3 text-right ${accColor}">${acc}%</td>
      <td class="px-5 py-3 text-right text-gray-500">${avgLat} ms</td>
    </tr>`;
  }).join('');

  Object.values(gCharts).forEach(c => c?.destroy());

  const gLabels = ['Luar Radius', 'Dalam Radius'];
  gCharts.bar = mkChart('G-bar-chart', {
    type: 'bar',
    data: { labels: gLabels, datasets: [
      { label: 'Benar (sesuai harapan)', data: [luarResult.benar, dalamResult.benar], backgroundColor: 'rgba(16,185,129,0.75)', borderRadius: 5 },
      { label: 'Salah (tidak sesuai)', data: [luarResult.salah, dalamResult.salah], backgroundColor: 'rgba(239,68,68,0.65)', borderRadius: 5 },
    ]},
    options: { ...CHART_DEFAULTS, scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f3f4f6' } }, x: { grid: { display: false } } } },
  });

  const avgLuar = luarResult.latencies.length ? Math.round(luarResult.latencies.reduce((a, b) => a + b, 0) / luarResult.latencies.length) : 0;
  const avgDalam = dalamResult.latencies.length ? Math.round(dalamResult.latencies.reduce((a, b) => a + b, 0) / dalamResult.latencies.length) : 0;
  gCharts.lat = mkChart('G-lat-chart', {
    type: 'bar',
    data: { labels: gLabels, datasets: [
      { label: 'Avg Latency (ms)', data: [avgLuar, avgDalam], backgroundColor: ['rgba(239,68,68,0.65)', 'rgba(99,102,241,0.75)'], borderRadius: 6 },
    ]},
    options: { ...CHART_DEFAULTS, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { callback: v => v + 'ms' }, grid: { color: '#f3f4f6' } }, x: { grid: { display: false } } } },
  });

  const { labels: hLabels, buckets, countInto } = makeHistBuckets([luarResult.latencies, dalamResult.latencies]);
  gCharts.hist = mkChart('G-hist-chart', {
    type: 'bar',
    data: { labels: hLabels, datasets: [
      { label: 'Luar Radius', data: buckets.map(lo => countInto(luarResult.latencies, lo)), backgroundColor: 'rgba(239,68,68,0.6)', borderRadius: 4 },
      { label: 'Dalam Radius', data: buckets.map(lo => countInto(dalamResult.latencies, lo)), backgroundColor: 'rgba(99,102,241,0.65)', borderRadius: 4 },
    ]},
    options: { ...CHART_DEFAULTS, scales: {
      x: { grid: { display: false }, title: { display: true, text: 'Rentang latensi (ms)', color: '#9ca3af', font: { size: 11 } } },
      y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#f3f4f6' }, title: { display: true, text: 'Frekuensi', color: '#9ca3af', font: { size: 11 } } },
    }},
  });
}

document.getElementById('G-run').addEventListener('click', async () => {
  const N     = Math.max(1, Math.min(30, parseInt(document.getElementById('G-n').value) || 10));
  const total = 1 + N + N;
  const btn   = document.getElementById('G-run');
  btn.disabled = true;
  document.getElementById('G-reset').classList.add('hidden');
  document.getElementById('G-results').classList.add('hidden');
  document.getElementById('G-prog-wrap').classList.remove('hidden');
  document.getElementById('G-err').classList.add('hidden');

  let step = 0;

  try {
    gSetProg(++step, total, 'Mengambil geofencing...');
    const geo = await getGeo();
    if (!geo) { document.getElementById('G-err').textContent = 'Tidak ada geofencing aktif.'; document.getElementById('G-err').classList.remove('hidden'); return; }

    const offsetDeg  = (parseFloat(geo.radius_meter) + 2000) / 111111;
    const outsideLat = parseFloat((parseFloat(geo.latitude) + offsetDeg).toFixed(7));
    const insideLat  = parseFloat(geo.latitude);
    const lng        = parseFloat(geo.longitude);

    // Fase A — Luar radius (harapan: ok=false, status_code=422)
    const luarResult = { benar: 0, salah: 0, latencies: [] };
    for (let i = 1; i <= N; i++) {
      gSetProg(++step, total, `[Luar radius] ${i}/${N}...`);
      const d = await proxyPost('{{ route('dashboard.uji-latensi.presensi') }}', {
        id_geofencing: geo.id, tipe_presensi: 'masuk', waktu: `${TODAY}T08:00:00`,
        latitude: outsideLat, longitude: lng, status_presensi: 'hadir',
      });
      luarResult.latencies.push(d.latency_ms);
      (!d.ok && d.status_code === 422) ? luarResult.benar++ : luarResult.salah++;
    }

    // Fase B — Dalam radius (harapan: ok=true, status_code=201)
    const dalamResult = { benar: 0, salah: 0, latencies: [] };
    for (let i = 1; i <= N; i++) {
      gSetProg(++step, total, `[Dalam radius] ${i}/${N}...`);
      const d = await proxyPost('{{ route('dashboard.uji-latensi.presensi') }}', {
        id_geofencing: geo.id, tipe_presensi: 'masuk', waktu: `${TODAY}T08:00:00`,
        latitude: insideLat, longitude: lng, status_presensi: 'hadir',
      });
      dalamResult.latencies.push(d.latency_ms);
      d.ok ? dalamResult.benar++ : dalamResult.salah++;
    }

    gSetProg(total, total, 'Selesai!');
    document.getElementById('G-results').classList.remove('hidden');
    gRenderResults(luarResult, dalamResult, geo);
    document.getElementById('G-results').scrollIntoView({ behavior: 'smooth', block: 'start' });
  } finally {
    btn.disabled = false;
    document.getElementById('G-reset').classList.remove('hidden');
    lucide.createIcons();
  }
});

document.getElementById('G-reset').addEventListener('click', () => {
  document.getElementById('G-results').classList.add('hidden');
  document.getElementById('G-prog-wrap').classList.add('hidden');
  document.getElementById('G-reset').classList.add('hidden');
  document.getElementById('G-prog-bar').style.width = '0%';
  Object.values(gCharts).forEach(c => c?.destroy()); gCharts = {};
});

// ─────────────────────────────────────────────────────────────
// TAB 3 — DETEKSI MOCK LOCATION
// ─────────────────────────────────────────────────────────────
const MOCK_SCENARIOS = [
  { no: 1, tahap: 'Tahap 1',     skenario: 'mockLocationDetected aktif',                          sinyal: { mock_location: true,  is_real_device: true,  is_rooted: false, is_dev_mode: false }, expected: 'HARD_BLOCK_MOCK' },
  { no: 2, tahap: 'Tahap 2',     skenario: 'isRealDevice = false (emulator)',                     sinyal: { mock_location: false, is_real_device: false, is_rooted: false, is_dev_mode: false }, expected: 'HARD_BLOCK_EMULATOR' },
  { no: 3, tahap: 'Tahap 3',     skenario: 'rootedDetected + developmentModeEnabled bersamaan',   sinyal: { mock_location: false, is_real_device: true,  is_rooted: true,  is_dev_mode: true  }, expected: 'HARD_BLOCK_SYNERGISTIC_THREAT' },
  { no: 4, tahap: 'Tahap 4a',    skenario: 'rootedDetected aktif saja',                          sinyal: { mock_location: false, is_real_device: true,  is_rooted: true,  is_dev_mode: false }, expected: 'SOFT_RISK' },
  { no: 5, tahap: 'Tahap 4b',    skenario: 'developmentModeEnabled aktif saja',                  sinyal: { mock_location: false, is_real_device: true,  is_rooted: false, is_dev_mode: true  }, expected: 'SOFT_RISK' },
  { no: 6, tahap: 'Tahap 5',     skenario: 'Semua sinyal aman, perangkat standar pabrikan',      sinyal: { mock_location: false, is_real_device: true,  is_rooted: false, is_dev_mode: false }, expected: 'SAFE' },
  { no: 7, tahap: 'Fail-Closed', skenario: 'Pustaka Safe Device error/crash saat ekstraksi sinyal', sinyal: { mock_location: false, is_real_device: true,  is_rooted: false, is_dev_mode: false }, expected: 'HARD_BLOCK_SENSOR_FAILED' },
];

const STATUS_COLOR = {
  HARD_BLOCK_MOCK: 'bg-rose-100 text-rose-700', HARD_BLOCK_EMULATOR: 'bg-rose-100 text-rose-700',
  HARD_BLOCK_SYNERGISTIC_THREAT: 'bg-rose-100 text-rose-700', HARD_BLOCK_SENSOR_FAILED: 'bg-rose-100 text-rose-700',
  SOFT_RISK: 'bg-amber-100 text-amber-700', SAFE: 'bg-emerald-100 text-emerald-700',
};

let mCharts = {};

function mSetProg(step, total, label) {
  setProgress('M-prog-bar', 'M-prog-lbl', 'M-prog-pct', step, total, label);
}

function mRenderResults(scenarioResults) {
  // Tabel 4.26
  document.getElementById('M-tbody').innerHTML = scenarioResults.map(r => {
    const acc = r.n > 0 ? Math.round((r.sesuai / r.n) * 10000) / 100 : 0;
    const avgLat = r.latencies.length ? Math.round(r.latencies.reduce((a, b) => a + b, 0) / r.latencies.length) : '-';
    const sc = STATUS_COLOR[r.expected] ?? 'bg-gray-100 text-gray-700';
    const accColor = acc === 100 ? 'text-emerald-700 font-bold' : 'text-rose-600 font-bold';
    return `<tr class="hover:bg-slate-50/60">
      <td class="px-4 py-3 text-gray-500">${r.no}</td>
      <td class="px-4 py-3 text-gray-500 text-xs">${r.tahap}</td>
      <td class="px-4 py-3 text-gray-700 text-xs max-w-[200px]">${r.skenario}</td>
      <td class="px-4 py-3"><span class="inline-flex rounded-lg px-2 py-0.5 text-xs font-semibold ${sc}">${r.expected}</span></td>
      <td class="px-4 py-3 text-right text-gray-500">${r.n}</td>
      <td class="px-4 py-3 text-right font-semibold text-emerald-700">${r.sesuai}</td>
      <td class="px-4 py-3 text-right font-semibold text-rose-600">${r.tidakSesuai}</td>
      <td class="px-4 py-3 text-right ${accColor}">${acc}%</td>
      <td class="px-4 py-3 text-right text-gray-500">${avgLat} ms</td>
    </tr>`;
  }).join('');

  // Statistik latensi gabungan
  const allLat = scenarioResults.flatMap(r => r.latencies);
  const st = calcStats(allLat);
  if (st) {
    const STAT_ROWS = [['N total request', 'n', ''], ['Minimum', 'min', ' ms'], ['Maksimum', 'max', ' ms'], ['Mean', 'mean', ' ms'], ['Median', 'median', ' ms'], ['Std. Deviasi', 'stdev', ' ms'], ['P90', 'p90', ' ms'], ['P95', 'p95', ' ms'], ['P99', 'p99', ' ms']];
    document.getElementById('M-stat-tbody').innerHTML = STAT_ROWS.map(([label, key, unit]) => {
      const v = st[key] ?? '-';
      return `<tr class="hover:bg-slate-50/60"><td class="px-5 py-2.5 text-gray-600">${label}</td><td class="px-5 py-2.5 text-right font-semibold text-amber-700">${v}${typeof v === 'number' ? unit : ''}</td></tr>`;
    }).join('');
  }

  // Charts
  Object.values(mCharts).forEach(c => c?.destroy());
  const shortLabels = scenarioResults.map(r => r.tahap);
  const accData = scenarioResults.map(r => r.n > 0 ? Math.round((r.sesuai / r.n) * 10000) / 100 : 0);
  const latData = scenarioResults.map(r => r.latencies.length ? Math.round(r.latencies.reduce((a, b) => a + b, 0) / r.latencies.length) : 0);

  mCharts.acc = mkChart('M-acc-chart', {
    type: 'bar',
    data: { labels: shortLabels, datasets: [{ label: 'Akurasi (%)', data: accData, backgroundColor: accData.map(v => v === 100 ? 'rgba(16,185,129,0.75)' : 'rgba(239,68,68,0.65)'), borderRadius: 5 }] },
    options: { ...CHART_DEFAULTS, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, max: 105, ticks: { callback: v => v + '%' }, grid: { color: '#f3f4f6' } }, x: { grid: { display: false }, ticks: { font: { size: 10 } } } } },
  });

  mCharts.lat = mkChart('M-lat-chart', {
    type: 'bar',
    data: { labels: shortLabels, datasets: [{ label: 'Avg Latency (ms)', data: latData, backgroundColor: 'rgba(245,158,11,0.7)', borderRadius: 5 }] },
    options: { ...CHART_DEFAULTS, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { callback: v => v + 'ms' }, grid: { color: '#f3f4f6' } }, x: { grid: { display: false }, ticks: { font: { size: 10 } } } } },
  });
}

document.getElementById('M-run').addEventListener('click', async () => {
  const N     = Math.max(1, Math.min(30, parseInt(document.getElementById('M-n').value) || 10));
  const total = MOCK_SCENARIOS.length * N;
  const btn   = document.getElementById('M-run');
  btn.disabled = true;
  document.getElementById('M-reset').classList.add('hidden');
  document.getElementById('M-results').classList.add('hidden');
  document.getElementById('M-prog-wrap').classList.remove('hidden');
  document.getElementById('M-err').classList.add('hidden');

  let step = 0;
  const scenarioResults = [];

  try {
    for (const sc of MOCK_SCENARIOS) {
      const result = { ...sc, n: 0, sesuai: 0, tidakSesuai: 0, latencies: [] };
      for (let i = 1; i <= N; i++) {
        mSetProg(++step, total, `[${sc.tahap}] Request ${i}/${N}...`);
        const d = await proxyPost('{{ route('dashboard.uji-latensi.log-deteksi') }}', {
          ...sc.sinyal,
          final_status: sc.expected,
          message: `Uji: ${sc.skenario}`,
        });
        result.n++;
        result.latencies.push(d.latency_ms);
        d.ok ? result.sesuai++ : result.tidakSesuai++;
      }
      scenarioResults.push(result);
    }

    mSetProg(total, total, 'Selesai!');
    document.getElementById('M-results').classList.remove('hidden');
    mRenderResults(scenarioResults);
    document.getElementById('M-results').scrollIntoView({ behavior: 'smooth', block: 'start' });
  } finally {
    btn.disabled = false;
    document.getElementById('M-reset').classList.remove('hidden');
    lucide.createIcons();
  }
});

document.getElementById('M-reset').addEventListener('click', () => {
  document.getElementById('M-results').classList.add('hidden');
  document.getElementById('M-prog-wrap').classList.add('hidden');
  document.getElementById('M-reset').classList.add('hidden');
  document.getElementById('M-prog-bar').style.width = '0%';
  Object.values(mCharts).forEach(c => c?.destroy()); mCharts = {};
});

// ─────────────────────────────────────────────────────────────
// TAB 4 — OFFLINE QUEUE (manual)
// ─────────────────────────────────────────────────────────────
let oChart = null;

document.getElementById('O-hitung').addEventListener('click', () => {
  const total    = parseInt(document.getElementById('O-total').value) || 30;
  const terkirim = Math.min(parseInt(document.getElementById('O-terkirim').value) || 0, total);
  const gagal    = total - terkirim;
  const pct      = total > 0 ? Math.round((terkirim / total) * 10000) / 100 : 0;
  const ket      = document.getElementById('O-ket').value || '-';

  document.getElementById('O-results').classList.remove('hidden');
  document.getElementById('O-tbody').innerHTML = `
    <tr class="hover:bg-slate-50/60">
      <td class="px-5 py-3 text-gray-500">1</td>
      <td class="px-5 py-3 text-gray-700">Presensi saat offline lalu koneksi pulih</td>
      <td class="px-5 py-3 text-right text-gray-500">${total}</td>
      <td class="px-5 py-3 text-right font-semibold text-emerald-700">${terkirim}</td>
      <td class="px-5 py-3 text-right font-semibold text-rose-600">${gagal}</td>
      <td class="px-5 py-3 text-right font-bold ${pct >= 90 ? 'text-emerald-700' : pct >= 70 ? 'text-amber-600' : 'text-rose-600'}">${pct}%</td>
      <td class="px-5 py-3 text-gray-500">${ket}</td>
    </tr>`;

  if (oChart) oChart.destroy();
  oChart = mkChart('O-chart', {
    type: 'doughnut',
    data: {
      labels: ['Terkirim', 'Gagal'],
      datasets: [{ data: [terkirim, gagal], backgroundColor: ['rgba(16,185,129,0.8)', 'rgba(239,68,68,0.7)'], borderWidth: 0 }],
    },
    options: {
      responsive: true, maintainAspectRatio: false,
      plugins: {
        legend: { display: true, position: 'bottom' },
        tooltip: { callbacks: { label: ctx => `${ctx.label}: ${ctx.raw} (${Math.round(ctx.raw / total * 100)}%)` } },
      },
      cutout: '65%',
    },
  });
  document.getElementById('O-results').scrollIntoView({ behavior: 'smooth', block: 'start' });
});

// ─────────────────────────────────────────────────────────────
// DATA NYATA — Tab Latensi (presensi_latency.log)
// ─────────────────────────────────────────────────────────────
let lrCharts = {};

document.getElementById('LR-load').addEventListener('click', async () => {
  const btn = document.getElementById('LR-load');
  btn.disabled = true; btn.textContent = 'Memuat...';
  document.getElementById('LR-err').classList.add('hidden');
  try {
    const resp = await fetch('{{ route('dashboard.uji-latensi.real.latensi') }}', { headers: { 'Accept': 'application/json' } });
    const d = await resp.json();
    if (d.error) { document.getElementById('LR-err').textContent = d.error; document.getElementById('LR-err').classList.remove('hidden'); return; }

    const masuk  = d.masuk.latencies  ?? [];
    const pulang = d.pulang.latencies ?? [];
    const sm = calcStats(masuk), sp = calcStats(pulang);

    document.getElementById('LR-total').textContent    = d.total_entries;
    document.getElementById('LR-masuk-n').textContent  = masuk.length;
    document.getElementById('LR-pulang-n').textContent = pulang.length;
    document.getElementById('LR-gagal').textContent    = (d.masuk.gagal ?? 0) + (d.pulang.gagal ?? 0);

    const STAT_ROWS = [['N','n',''],['Min','min',' ms'],['Max','max',' ms'],['Mean','mean',' ms'],['Median','median',' ms'],['Std Dev','stdev',' ms'],['P90','p90',' ms'],['P95','p95',' ms'],['P99','p99',' ms']];
    document.getElementById('LR-tbody').innerHTML = STAT_ROWS.map(([label, key, unit]) => {
      const vm = sm?.[key] ?? '-', vp = sp?.[key] ?? '-';
      return `<tr class="hover:bg-amber-50/40"><td class="px-5 py-2 text-gray-600">${label}</td><td class="px-5 py-2 text-right font-semibold text-amber-700">${vm !== '-' ? vm + unit : '-'}</td><td class="px-5 py-2 text-right font-semibold text-amber-600">${vp !== '-' ? vp + unit : '-'}</td></tr>`;
    }).join('');

    Object.values(lrCharts).forEach(c => c?.destroy());
    const labels = Array.from({ length: Math.max(masuk.length, pulang.length) }, (_, i) => `#${i + 1}`);
    lrCharts.line = mkChart('LR-line-chart', {
      type: 'line',
      data: { labels, datasets: [
        { label: 'Masuk (ms)',  data: masuk,  borderColor: '#f97316', backgroundColor: 'rgba(249,115,22,0.07)', fill: true, tension: 0.35, pointRadius: 3 },
        { label: 'Pulang (ms)', data: pulang, borderColor: '#eab308', backgroundColor: 'rgba(234,179,8,0.07)',  fill: true, tension: 0.35, pointRadius: 3 },
      ]},
      options: { ...CHART_DEFAULTS, scales: { y: { beginAtZero: true, ticks: { callback: v => v + 'ms' }, grid: { color: '#fef3c7' } }, x: { grid: { display: false } } }, interaction: { intersect: false, mode: 'index' } },
    });
    const sKeys = ['min_ms', 'mean_ms', 'median_ms', 'p90_ms', 'p95_ms', 'max_ms'];
    lrCharts.bar = mkChart('LR-bar-chart', {
      type: 'bar',
      data: { labels: ['Min', 'Mean', 'Median', 'P90', 'P95', 'Max'], datasets: [
        { label: 'Masuk (ms)',  data: sKeys.map(k => sm?.[k] ?? 0), backgroundColor: 'rgba(249,115,22,0.75)', borderRadius: 5 },
        { label: 'Pulang (ms)', data: sKeys.map(k => sp?.[k] ?? 0), backgroundColor: 'rgba(234,179,8,0.75)',  borderRadius: 5 },
      ]},
      options: { ...CHART_DEFAULTS, scales: { y: { beginAtZero: true, ticks: { callback: v => v + 'ms' }, grid: { color: '#fef3c7' } }, x: { grid: { display: false } } } },
    });

    document.getElementById('LR-results').classList.remove('hidden');
    document.getElementById('LR-results').scrollIntoView({ behavior: 'smooth', block: 'start' });
  } catch (e) { document.getElementById('LR-err').textContent = 'Error: ' + e.message; document.getElementById('LR-err').classList.remove('hidden'); }
  finally { btn.disabled = false; btn.innerHTML = '<i data-lucide="database" class="h-4 w-4 inline mr-1"></i>Muat Data Nyata'; lucide.createIcons(); }
});

// ─────────────────────────────────────────────────────────────
// DATA NYATA — Tab Geofence (presensi table + log)
// ─────────────────────────────────────────────────────────────
let grChart = null;

document.getElementById('GR-load').addEventListener('click', async () => {
  const btn = document.getElementById('GR-load');
  btn.disabled = true; btn.textContent = 'Memuat...';
  document.getElementById('GR-err').classList.add('hidden');
  try {
    const resp = await fetch('{{ route('dashboard.uji-latensi.real.geofence') }}', { headers: { 'Accept': 'application/json' } });
    const d = await resp.json();
    if (d.error) { document.getElementById('GR-err').textContent = d.error; document.getElementById('GR-err').classList.remove('hidden'); return; }

    const dalam  = d.dalam_radius;
    const ditolak = d.ditolak_422;

    document.getElementById('GR-tbody').innerHTML = [
      { no: 1, skenario: 'Lokasi di dalam radius', sampel: dalam.count,   benar: dalam.count,  salah: 0,             akurasi: dalam.count > 0 ? 100 : null, catatan: 'Semua presensi tersimpan = lolos geofencing' },
      { no: 2, skenario: 'Lokasi di luar radius (ditolak)', sampel: ditolak.count, benar: ditolak.count, salah: 0, akurasi: ditolak.count > 0 ? 100 : null, catatan: ditolak.catatan },
    ].map(r => {
      const accColor = r.akurasi === 100 ? 'text-emerald-700 font-bold' : r.akurasi === null ? 'text-gray-400' : 'text-rose-600';
      return `<tr class="hover:bg-amber-50/40">
        <td class="px-5 py-3 text-amber-600">${r.no}</td>
        <td class="px-5 py-3 font-medium text-gray-800">${r.skenario}</td>
        <td class="px-5 py-3 text-right text-gray-500">${r.sampel}</td>
        <td class="px-5 py-3 text-right font-semibold text-emerald-700">${r.benar}</td>
        <td class="px-5 py-3 text-right text-gray-400">${r.salah}</td>
        <td class="px-5 py-3 text-right ${accColor}">${r.akurasi !== null ? r.akurasi + '%' : '—'}</td>
        <td class="px-5 py-3 text-xs text-gray-400 italic">${r.catatan}</td>
      </tr>`;
    }).join('');

    if (grChart) grChart.destroy();
    const jarakList = dalam.jarak_list_meter ?? [];
    if (jarakList.length > 0) {
      const maxJ = Math.max(...jarakList);
      const bSize = Math.max(1, Math.ceil(maxJ / 10));
      const buckets = [];
      for (let lo = 0; lo <= maxJ; lo += bSize) buckets.push(lo);
      const countInto = (arr, lo) => arr.filter(v => v >= lo && v < lo + bSize).length;
      grChart = mkChart('GR-chart', {
        type: 'bar',
        data: { labels: buckets.map(lo => `${lo}–${lo + bSize}m`), datasets: [
          { label: 'Jumlah presensi', data: buckets.map(lo => countInto(jarakList, lo)), backgroundColor: 'rgba(245,158,11,0.7)', borderRadius: 4 },
        ]},
        options: { ...CHART_DEFAULTS, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, ticks: { precision: 0 }, grid: { color: '#fef3c7' } }, x: { grid: { display: false }, title: { display: true, text: 'Jarak dari pusat geofence (m)', color: '#92400e', font: { size: 11 } } } } },
      });
    }

    document.getElementById('GR-results').classList.remove('hidden');
    document.getElementById('GR-results').scrollIntoView({ behavior: 'smooth', block: 'start' });
  } catch (e) { document.getElementById('GR-err').textContent = 'Error: ' + e.message; document.getElementById('GR-err').classList.remove('hidden'); }
  finally { btn.disabled = false; btn.innerHTML = '<i data-lucide="database" class="h-4 w-4 inline mr-1"></i>Muat Data Nyata'; lucide.createIcons(); }
});

// ─────────────────────────────────────────────────────────────
// DATA NYATA — Tab Mock (log_deteksi table)
// ─────────────────────────────────────────────────────────────
let mrCharts = {};

const STATUS_COLORS_DIST = {
  HARD_BLOCK_MOCK: 'rgba(239,68,68,0.75)', HARD_BLOCK_EMULATOR: 'rgba(220,38,38,0.65)',
  HARD_BLOCK_SYNERGISTIC_THREAT: 'rgba(185,28,28,0.7)', HARD_BLOCK_SENSOR_FAILED: 'rgba(127,29,29,0.65)',
  SOFT_RISK: 'rgba(245,158,11,0.75)', SAFE: 'rgba(16,185,129,0.75)',
};

document.getElementById('MR-load').addEventListener('click', async () => {
  const btn = document.getElementById('MR-load');
  btn.disabled = true; btn.textContent = 'Memuat...';
  document.getElementById('MR-err').classList.add('hidden');
  try {
    const resp = await fetch('{{ route('dashboard.uji-latensi.real.mock') }}', { headers: { 'Accept': 'application/json' } });
    const d = await resp.json();
    if (d.error) { document.getElementById('MR-err').textContent = d.error; document.getElementById('MR-err').classList.remove('hidden'); return; }

    document.getElementById('MR-total').textContent = d.total_logs;
    const sc = STATUS_COLOR ?? {};

    document.getElementById('MR-tbody').innerHTML = (d.scenarios ?? []).map(r => {
      const accColor = r.akurasi === null ? 'text-gray-400' : r.akurasi === 100 ? 'text-emerald-700 font-bold' : 'text-rose-600 font-bold';
      const accTxt = r.akurasi !== null ? r.akurasi + '%' : '—';
      const badgeClass = STATUS_COLOR?.[r.expected] ?? 'bg-gray-100 text-gray-700';
      return `<tr class="hover:bg-amber-50/40">
        <td class="px-4 py-3 text-amber-500">${r.no}</td>
        <td class="px-4 py-3 text-xs text-gray-500">${r.tahap}</td>
        <td class="px-4 py-3 text-xs text-gray-700 max-w-[180px]">${r.skenario}</td>
        <td class="px-4 py-3"><span class="inline-flex rounded-lg px-2 py-0.5 text-xs font-semibold ${badgeClass}">${r.expected}</span></td>
        <td class="px-4 py-3 text-right text-gray-500">${r.total}</td>
        <td class="px-4 py-3 text-right font-semibold text-emerald-700">${r.sesuai}</td>
        <td class="px-4 py-3 text-right font-semibold text-rose-600">${r.tidak_sesuai}</td>
        <td class="px-4 py-3 text-right ${accColor}">${accTxt}</td>
      </tr>`;
    }).join('');

    Object.values(mrCharts).forEach(c => c?.destroy());
    const scLabels = (d.scenarios ?? []).map(r => r.tahap);
    const accData  = (d.scenarios ?? []).map(r => r.akurasi ?? 0);
    mrCharts.acc = mkChart('MR-acc-chart', {
      type: 'bar',
      data: { labels: scLabels, datasets: [{ label: 'Akurasi (%)', data: accData, backgroundColor: accData.map(v => v === 100 ? 'rgba(16,185,129,0.75)' : v > 0 ? 'rgba(245,158,11,0.75)' : 'rgba(156,163,175,0.5)'), borderRadius: 5 }] },
      options: { ...CHART_DEFAULTS, plugins: { legend: { display: false } }, scales: { y: { beginAtZero: true, max: 105, ticks: { callback: v => v + '%' }, grid: { color: '#fef3c7' } }, x: { grid: { display: false }, ticks: { font: { size: 10 } } } } },
    });

    const perStatus = d.per_status ?? {};
    const statusKeys = Object.keys(perStatus);
    mrCharts.dist = mkChart('MR-dist-chart', {
      type: 'doughnut',
      data: { labels: statusKeys, datasets: [{ data: statusKeys.map(k => perStatus[k]), backgroundColor: statusKeys.map(k => STATUS_COLORS_DIST[k] ?? 'rgba(156,163,175,0.6)'), borderWidth: 0 }] },
      options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { position: 'right', labels: { font: { size: 10 }, boxWidth: 12 } } }, cutout: '55%' },
    });

    document.getElementById('MR-results').classList.remove('hidden');
    document.getElementById('MR-results').scrollIntoView({ behavior: 'smooth', block: 'start' });
  } catch (e) { document.getElementById('MR-err').textContent = 'Error: ' + e.message; document.getElementById('MR-err').classList.remove('hidden'); }
  finally { btn.disabled = false; btn.innerHTML = '<i data-lucide="database" class="h-4 w-4 inline mr-1"></i>Muat Data Nyata'; lucide.createIcons(); }
});
</script>
@endpush
