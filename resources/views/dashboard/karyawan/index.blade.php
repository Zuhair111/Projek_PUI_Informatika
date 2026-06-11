@extends('dashboard.layouts.app', ['title' => 'Manajemen Karyawan'])

@section('content')
  {{-- ─── Header ──────────────────────────────────────── --}}
  <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
    <div>
      <h2 class="text-xl font-bold text-gray-900">Daftar Karyawan</h2>
      <p class="mt-0.5 text-sm text-gray-400">Kelola data informasi karyawan dan status presensi.</p>
    </div>
    <a href="{{ route('dashboard.karyawan.create') }}"
      class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-indigo-700 hover:shadow">
      <i data-lucide="user-plus" class="h-4 w-4"></i>
      Tambah Karyawan
    </a>
  </div>

  {{-- ─── Search / Filter ─────────────────────────────── --}}
  <div class="mb-5 overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
    <form method="GET" class="flex flex-col gap-3 p-4 sm:flex-row">
      <div class="relative flex-1">
        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3.5">
          <i data-lucide="search" class="h-4 w-4 text-gray-400"></i>
        </div>
        <input type="text" name="search" value="{{ $search }}"
          placeholder="Cari nama, NIP, departemen, atau jabatan..."
          class="block w-full rounded-xl border border-gray-200 bg-gray-50 py-2.5 pl-10 pr-4 text-sm text-gray-900 outline-none transition-all placeholder:text-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
      </div>
      <button type="submit"
        class="inline-flex items-center justify-center gap-2 rounded-xl border border-indigo-200 bg-indigo-50 px-5 py-2.5 text-sm font-semibold text-indigo-700 transition-colors hover:bg-indigo-100">
        Terapkan Filter
      </button>
    </form>
  </div>

  {{-- ─── Table ───────────────────────────────────────── --}}
  <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
    <div class="overflow-x-auto">
      <table class="min-w-full text-sm text-left align-middle">
        <thead>
          <tr class="border-b border-gray-100 bg-gray-50/70">
            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Karyawan</th>
            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Departemen</th>
            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Jabatan</th>
            <th class="px-5 py-4 text-xs font-bold uppercase tracking-wider text-gray-400">Status</th>
            <th class="px-5 py-4 text-right text-xs font-bold uppercase tracking-wider text-gray-400">Aksi</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-gray-50">
          @forelse ($karyawan as $item)
            <tr class="transition-colors hover:bg-slate-50/60">
              <td class="px-5 py-4">
                <div class="flex items-center gap-3">
                  <div class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-sm font-bold text-indigo-700">
                    {{ substr($item->nama, 0, 1) }}
                  </div>
                  <div>
                    <p class="font-semibold text-gray-900">{{ $item->nama }}</p>
                    <p class="text-xs text-gray-400">NIP: {{ $item->nip }}</p>
                  </div>
                </div>
              </td>
              <td class="px-5 py-4 text-gray-500">{{ $item->departemen }}</td>
              <td class="px-5 py-4">
                <span class="inline-flex items-center rounded-lg bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-600">
                  {{ $item->jabatan }}
                </span>
              </td>
              <td class="px-5 py-4">
                @if ($item->is_active)
                  <span class="inline-flex items-center gap-1.5 rounded-full border border-emerald-200/60 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-emerald-500"></span>
                    Aktif
                  </span>
                @else
                  <span class="inline-flex items-center gap-1.5 rounded-full border border-rose-200/60 bg-rose-50 px-2.5 py-1 text-xs font-semibold text-rose-700">
                    <span class="h-1.5 w-1.5 rounded-full bg-rose-500"></span>
                    Nonaktif
                  </span>
                @endif
              </td>
              <td class="px-5 py-4 text-right">
                <div class="flex items-center justify-end gap-1.5">
                  <a href="{{ route('dashboard.karyawan.edit', $item->id) }}"
                    class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-amber-50 text-amber-600 transition-colors hover:bg-amber-100 hover:text-amber-800"
                    title="Edit Data">
                    <i data-lucide="pencil" class="h-3.5 w-3.5"></i>
                  </a>
                  <form method="POST" action="{{ route('dashboard.karyawan.destroy', $item->id) }}"
                    onsubmit="return confirm('Hapus data karyawan ini secara permanen?')" class="inline-block">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                      class="inline-flex h-8 w-8 items-center justify-center rounded-lg bg-rose-50 text-rose-600 transition-colors hover:bg-rose-100 hover:text-rose-800"
                      title="Hapus Data">
                      <i data-lucide="trash-2" class="h-3.5 w-3.5"></i>
                    </button>
                  </form>
                </div>
              </td>
            </tr>
          @empty
            <tr>
              <td colspan="5" class="px-5 py-16 text-center">
                <div class="mx-auto flex max-w-xs flex-col items-center">
                  <div class="flex h-14 w-14 items-center justify-center rounded-2xl bg-gray-100 text-gray-400">
                    <i data-lucide="users" class="h-7 w-7"></i>
                  </div>
                  <p class="mt-3 text-sm font-semibold text-gray-700">Belum ada karyawan</p>
                  <p class="mt-1 text-xs text-gray-400">Tidak ada data yang cocok dengan filter saat ini.</p>
                  @if($search)
                    <a href="{{ route('dashboard.karyawan.index') }}"
                      class="mt-4 text-xs font-semibold text-indigo-600 hover:underline">
                      Hapus filter
                    </a>
                  @endif
                </div>
              </td>
            </tr>
          @endforelse
        </tbody>
      </table>
    </div>
  </div>

  @if($karyawan->hasPages())
    <div class="mt-5 flex justify-end">
      {{ $karyawan->links() }}
    </div>
  @endif
@endsection
