@extends('dashboard.layouts.app', ['title' => 'Dashboard Utama'])

@section('content')
  {{-- ─── Stat Cards ──────────────────────────────────── --}}
  <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-5">

    {{-- Karyawan Aktif --}}
    <div class="group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition-all hover:shadow-md hover:-translate-y-0.5">
      <div class="absolute inset-x-0 top-0 h-0.5 bg-indigo-500"></div>
      <div class="flex items-start justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Karyawan Aktif</p>
          <p class="mt-3 text-3xl font-bold tracking-tight text-gray-900">{{ $summary['total_karyawan_aktif'] }}</p>
          <p class="mt-1 text-xs text-gray-400">Total terdaftar</p>
        </div>
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-indigo-50 text-indigo-600 transition-colors group-hover:bg-indigo-100">
          <i data-lucide="users" class="h-5 w-5"></i>
        </div>
      </div>
    </div>

    {{-- Total Presensi --}}
    <div class="group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition-all hover:shadow-md hover:-translate-y-0.5">
      <div class="absolute inset-x-0 top-0 h-0.5 bg-blue-500"></div>
      <div class="flex items-start justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Total Presensi</p>
          <p class="mt-3 text-3xl font-bold tracking-tight text-gray-900">{{ $summary['total_presensi_hari_ini'] }}</p>
          <p class="mt-1 text-xs text-gray-400">Hari ini</p>
        </div>
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-blue-50 text-blue-600 transition-colors group-hover:bg-blue-100">
          <i data-lucide="file-check-2" class="h-5 w-5"></i>
        </div>
      </div>
    </div>

    {{-- Hadir --}}
    <div class="group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition-all hover:shadow-md hover:-translate-y-0.5">
      <div class="absolute inset-x-0 top-0 h-0.5 bg-emerald-500"></div>
      <div class="flex items-start justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Hadir</p>
          <p class="mt-3 text-3xl font-bold tracking-tight text-emerald-600">{{ $summary['total_hadir'] }}</p>
          <p class="mt-1 text-xs text-gray-400">Tepat waktu</p>
        </div>
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-emerald-50 text-emerald-600 transition-colors group-hover:bg-emerald-100">
          <i data-lucide="check-circle" class="h-5 w-5"></i>
        </div>
      </div>
    </div>

    {{-- Terlambat --}}
    <div class="group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition-all hover:shadow-md hover:-translate-y-0.5">
      <div class="absolute inset-x-0 top-0 h-0.5 bg-amber-500"></div>
      <div class="flex items-start justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Terlambat</p>
          <p class="mt-3 text-3xl font-bold tracking-tight text-amber-500">{{ $summary['total_terlambat'] }}</p>
          <p class="mt-1 text-xs text-gray-400">Hari ini</p>
        </div>
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-amber-50 text-amber-500 transition-colors group-hover:bg-amber-100">
          <i data-lucide="clock" class="h-5 w-5"></i>
        </div>
      </div>
    </div>

    {{-- Tidak Hadir --}}
    <div class="group relative overflow-hidden rounded-2xl border border-gray-100 bg-white p-5 shadow-sm transition-all hover:shadow-md hover:-translate-y-0.5">
      <div class="absolute inset-x-0 top-0 h-0.5 bg-rose-500"></div>
      <div class="flex items-start justify-between">
        <div>
          <p class="text-xs font-semibold uppercase tracking-wider text-gray-400">Tidak Hadir</p>
          <p class="mt-3 text-3xl font-bold tracking-tight text-rose-600">{{ $summary['total_tidak_hadir'] }}</p>
          <p class="mt-1 text-xs text-gray-400">Hari ini</p>
        </div>
        <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-xl bg-rose-50 text-rose-600 transition-colors group-hover:bg-rose-100">
          <i data-lucide="x-circle" class="h-5 w-5"></i>
        </div>
      </div>
    </div>
  </section>

  {{-- ─── Chart ───────────────────────────────────────── --}}
  <section class="mt-6 overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
    <div class="flex items-center justify-between border-b border-gray-100 px-6 py-4">
      <div>
        <h2 class="text-base font-bold text-gray-900">Grafik Kehadiran Mingguan</h2>
        <p class="mt-0.5 text-xs text-gray-400">Rekap 7 hari terakhir</p>
      </div>
      <button class="flex items-center gap-1.5 rounded-xl border border-gray-200 bg-white px-3.5 py-2 text-xs font-semibold text-gray-600 transition-all hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800">
        <i data-lucide="download" class="h-3.5 w-3.5"></i>
        Export
      </button>
    </div>
    <div class="p-5 sm:p-7">
      <div class="relative h-72 w-full">
        <canvas id="weeklyAttendanceChart"></canvas>
      </div>
    </div>
  </section>
@endsection

@push('scripts')
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    const labels = @json($weeklyLabels);
    const values = @json($weeklyValues);

    const ctx = document.getElementById('weeklyAttendanceChart').getContext('2d');

    const gradient = ctx.createLinearGradient(0, 0, 0, 288);
    gradient.addColorStop(0, 'rgba(99, 102, 241, 0.18)');
    gradient.addColorStop(1, 'rgba(99, 102, 241, 0)');

    new Chart(ctx, {
      type: 'line',
      data: {
        labels,
        datasets: [{
          label: 'Kehadiran',
          data: values,
          borderColor: '#6366f1',
          backgroundColor: gradient,
          borderWidth: 2.5,
          fill: true,
          tension: 0.4,
          pointBackgroundColor: '#ffffff',
          pointBorderColor: '#6366f1',
          pointBorderWidth: 2,
          pointRadius: 4,
          pointHoverRadius: 7,
          pointHoverBackgroundColor: '#6366f1',
          pointHoverBorderColor: '#fff',
          pointHoverBorderWidth: 2,
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            backgroundColor: '#1e1b4b',
            padding: 12,
            titleFont: { size: 12, family: "'Plus Jakarta Sans', sans-serif", weight: '500' },
            bodyFont: { size: 15, weight: '700', family: "'Plus Jakarta Sans', sans-serif" },
            displayColors: false,
            cornerRadius: 10,
            callbacks: {
              title: (items) => items[0].label,
              label: (item) => `${item.raw} orang hadir`,
            }
          }
        },
        scales: {
          x: {
            grid: { display: false },
            border: { display: false },
            ticks: { font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 }, color: '#9ca3af' }
          },
          y: {
            beginAtZero: true,
            grid: { color: '#f3f4f6' },
            border: { display: false, dash: [4, 4] },
            ticks: {
              precision: 0,
              font: { family: "'Plus Jakarta Sans', sans-serif", size: 11 },
              color: '#9ca3af',
              stepSize: 1
            }
          }
        },
        interaction: { intersect: false, mode: 'index' },
      }
    });
  </script>
@endpush
