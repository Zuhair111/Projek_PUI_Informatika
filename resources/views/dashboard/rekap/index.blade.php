@extends('dashboard.layouts.app', ['title' => 'Rekap Presensi'])

@section('content')
  {{-- ─── Header ──────────────────────────────────────── --}}
  <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h2 class="text-xl font-bold text-gray-900">Rekap & Laporan Presensi</h2>
      <p class="mt-0.5 text-sm text-gray-400">Monitor dan unduh laporan kehadiran karyawan.</p>
    </div>
    <div class="flex flex-wrap items-center gap-2">
      <a href="{{ route('dashboard.rekap.export.pdf', request()->query()) }}"
        class="inline-flex items-center gap-1.5 rounded-xl border border-rose-200 bg-rose-50 px-4 py-2 text-sm font-semibold text-rose-700 transition-all hover:bg-rose-100 hover:shadow-sm">
        <i data-lucide="file-text" class="h-4 w-4"></i>
        Export PDF
      </a>
      <a href="{{ route('dashboard.rekap.export.excel', request()->query()) }}"
        class="inline-flex items-center gap-1.5 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-2 text-sm font-semibold text-emerald-700 transition-all hover:bg-emerald-100 hover:shadow-sm">
        <i data-lucide="table-2" class="h-4 w-4"></i>
        Export Excel
      </a>
    </div>
  </div>

  {{-- ─── Filter Form ─────────────────────────────────── --}}
  <div class="mb-5 overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
    <div class="border-b border-gray-100 px-5 py-3.5">
      <p class="text-sm font-semibold text-gray-700">Filter Data</p>
    </div>
    <form method="GET" class="grid gap-4 p-5 sm:grid-cols-2 lg:grid-cols-4">
      <div>
        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-400">Tanggal</label>
        <input type="date" name="tanggal" value="{{ $filters['tanggal'] }}"
          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 outline-none transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
      </div>
      <div>
        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-400">Departemen</label>
        <input name="departemen" value="{{ $filters['departemen'] }}" placeholder="Semua departemen"
          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 outline-none transition-all placeholder:text-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
      </div>
      <div>
        <label class="mb-1.5 block text-xs font-semibold uppercase tracking-wider text-gray-400">Nama Karyawan</label>
        <input name="nama" value="{{ $filters['nama'] }}" placeholder="Cari nama..."
          class="w-full rounded-xl border border-gray-200 bg-gray-50 px-3.5 py-2.5 text-sm text-gray-900 outline-none transition-all placeholder:text-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
      </div>
      <div class="flex items-end">
        <button type="submit"
          class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-indigo-700 hover:shadow">
          <i data-lucide="search" class="h-4 w-4"></i>
          Terapkan Filter
        </button>
      </div>
    </form>
  </div>

  {{-- ─── Table ───────────────────────────────────────── --}}
  <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="border-b border-gray-100 bg-gray-50/70 text-left">
            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Nama Karyawan</th>
            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Departemen</th>
            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Tanggal</th>
            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Masuk</th>
            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Pulang</th>
            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Status</th>
            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Deteksi Lokasi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          @forelse ($rows as $row)
            @php
              $status = strtolower($row->status ?? '');
              $statusConfig = match(true) {
                str_contains($status, 'hadir') && !str_contains($status, 'tidak') && !str_contains($status, 'terlambat')
                  => ['bg-emerald-50 text-emerald-700 border border-emerald-200/60', 'Hadir'],
                str_contains($status, 'terlambat')
                  => ['bg-amber-50 text-amber-700 border border-amber-200/60', 'Terlambat'],
                str_contains($status, 'tidak') || str_contains($status, 'absen')
                  => ['bg-rose-50 text-rose-700 border border-rose-200/60', 'Tidak Hadir'],
                default => ['bg-gray-100 text-gray-600', ucfirst($row->status ?? '-')],
              };
            @endphp
            <tr class="transition-colors hover:bg-slate-50/60">
              <td class="px-5 py-3.5 font-medium text-gray-900">{{ $row->nama_karyawan }}</td>
              <td class="px-5 py-3.5 text-gray-500">{{ $row->departemen }}</td>
              <td class="px-5 py-3.5 text-gray-500">{{ $row->tanggal }}</td>
              <td class="px-5 py-3.5 font-mono text-sm text-gray-600">{{ $row->check_in_at ?? '—' }}</td>
              <td class="px-5 py-3.5 font-mono text-sm text-gray-600">{{ $row->check_out_at ?? '—' }}</td>
              <td class="px-5 py-3.5">
                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold {{ $statusConfig[0] }}">
                  <span class="h-1.5 w-1.5 rounded-full
                    {{ str_contains($statusConfig[0], 'emerald') ? 'bg-emerald-500' : (str_contains($statusConfig[0], 'amber') ? 'bg-amber-500' : (str_contains($statusConfig[0], 'rose') ? 'bg-rose-500' : 'bg-gray-400')) }}">
                  </span>
                  {{ $statusConfig[1] }}
                </span>
              </td>
              <td class="px-5 py-3.5 text-gray-500 text-xs">{{ $row->hasil_deteksi ?? '—' }}</td>
            </tr>
          @empty
            <tr>
              <td colspan="7" class="px-5 py-14 text-center">
                <div class="mx-auto flex max-w-xs flex-col items-center">
                  <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400">
                    <i data-lucide="calendar-x" class="h-6 w-6"></i>
                  </div>
                  <p class="mt-3 text-sm font-semibold text-gray-700">Tidak ada data rekap</p>
                  <p class="mt-1 text-xs text-gray-400">Coba ubah filter pencarian Anda.</p>
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  @if($rows->hasPages())
    <div class="mt-5 flex justify-end">
      {{ $rows->links() }}
    </div>
  @endif
@endsection
