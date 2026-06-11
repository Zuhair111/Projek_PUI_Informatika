@extends('dashboard.layouts.app', ['title' => 'Monitoring Log Deteksi'])

@section('content')
  {{-- ─── Header ──────────────────────────────────────── --}}
  <div class="mb-6">
    <h2 class="text-xl font-bold text-gray-900">Monitoring Log Deteksi</h2>
    <p class="mt-0.5 text-sm text-gray-400">Pantau aktivitas deteksi mock location dan keamanan perangkat.</p>
  </div>

  {{-- ─── Table ───────────────────────────────────────── --}}
  <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm">
        <thead>
          <tr class="border-b border-gray-100 bg-gray-50/70 text-left">
            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Karyawan</th>
            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Waktu</th>
            <th class="px-5 py-4 text-center text-xs font-bold uppercase tracking-wider text-gray-400">Mock GPS</th>
            <th class="px-5 py-4 text-center text-xs font-bold uppercase tracking-wider text-gray-400">Real Device</th>
            <th class="px-5 py-4 text-center text-xs font-bold uppercase tracking-wider text-gray-400">Rooted</th>
            <th class="px-5 py-4 text-center text-xs font-bold uppercase tracking-wider text-gray-400">Dev Mode</th>
            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Status</th>
            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Keputusan</th>
            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Pesan</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          @forelse ($rows as $row)
            @php
              $isHardBlock = str_starts_with((string) $row->final_status, 'HARD_BLOCK');
              $isSoftRisk  = $row->final_status === 'SOFT_RISK';

              $statusLabel = match ($row->final_status) {
                'HARD_BLOCK_MOCK'               => 'Hard Block Mock',
                'HARD_BLOCK_EMULATOR'           => 'Hard Block Emulator',
                'HARD_BLOCK_SYNERGISTIC_THREAT' => 'Synergistic Threat',
                'HARD_BLOCK_SENSOR_FAILED'      => 'Sensor Failed',
                'SOFT_RISK'                     => 'Soft Risk',
                default                         => 'Safe',
              };

              $rowBg = $isHardBlock ? 'bg-rose-50/60' : ($isSoftRisk ? 'bg-amber-50/50' : '');
            @endphp
            <tr class="transition-colors hover:brightness-[0.98] {{ $rowBg }}">
              <td class="px-5 py-3.5 font-medium text-gray-900">{{ $row->nama_karyawan }}</td>
              <td class="px-5 py-3.5 font-mono text-xs text-gray-500">{{ $row->detected_at }}</td>

              {{-- Boolean: Mock Location (danger if true) --}}
              <td class="px-5 py-3.5 text-center">
                @if($row->mock_location)
                  <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-rose-100 text-rose-600 mx-auto">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                  </span>
                @else
                  <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 mx-auto">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                  </span>
                @endif
              </td>

              {{-- Boolean: Real Device (danger if false) --}}
              <td class="px-5 py-3.5 text-center">
                @if($row->is_real_device)
                  <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 mx-auto">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                  </span>
                @else
                  <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-rose-100 text-rose-600 mx-auto">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                  </span>
                @endif
              </td>

              {{-- Boolean: Rooted (danger if true) --}}
              <td class="px-5 py-3.5 text-center">
                @if($row->is_rooted)
                  <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-rose-100 text-rose-600 mx-auto">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                  </span>
                @else
                  <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 mx-auto">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                  </span>
                @endif
              </td>

              {{-- Boolean: Dev Mode (danger if true) --}}
              <td class="px-5 py-3.5 text-center">
                @if($row->is_dev_mode)
                  <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-amber-100 text-amber-600 mx-auto">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                  </span>
                @else
                  <span class="inline-flex h-6 w-6 items-center justify-center rounded-full bg-emerald-100 text-emerald-600 mx-auto">
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
                  </span>
                @endif
              </td>

              <td class="px-5 py-3.5">
                <span class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1 text-xs font-semibold
                  {{ $isHardBlock ? 'bg-rose-100 text-rose-700' : ($isSoftRisk ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                  {{ $statusLabel }}
                </span>
              </td>

              <td class="px-5 py-3.5">
                <span class="inline-flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-bold
                  {{ $isHardBlock ? 'bg-rose-600 text-white' : ($isSoftRisk ? 'bg-amber-500 text-white' : 'bg-emerald-500 text-white') }}">
                  {{ $isHardBlock ? 'Ditolak' : ($isSoftRisk ? 'Peringatan' : 'Lolos') }}
                </span>
              </td>

              <td class="px-5 py-3.5 max-w-[200px] truncate text-xs text-gray-500" title="{{ $row->message ?? '' }}">
                {{ $row->message ?? '—' }}
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="9" class="px-5 py-14 text-center">
                <div class="mx-auto flex max-w-xs flex-col items-center">
                  <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400">
                    <i data-lucide="shield-check" class="h-6 w-6"></i>
                  </div>
                  <p class="mt-3 text-sm font-semibold text-gray-700">Belum ada log deteksi</p>
                  <p class="mt-1 text-xs text-gray-400">Log akan muncul saat karyawan melakukan presensi.</p>
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
