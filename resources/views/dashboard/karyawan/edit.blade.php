@extends('dashboard.layouts.app', ['title' => 'Edit Karyawan'])

@section('content')
  <div class="mb-6">
    <a href="{{ route('dashboard.karyawan.index') }}"
      class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 transition-colors hover:text-gray-700">
      <i data-lucide="arrow-left" class="h-4 w-4"></i>
      Kembali ke Daftar Karyawan
    </a>
  </div>

  <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
    <div class="flex items-center gap-4 border-b border-gray-100 px-6 py-5">
      <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-indigo-100 text-lg font-bold text-indigo-700">
        {{ substr($item->nama, 0, 1) }}
      </div>
      <div>
        <h2 class="text-base font-bold text-gray-900">{{ $item->nama }}</h2>
        <p class="text-sm text-gray-400">NIP: {{ $item->nip }} · {{ $item->jabatan }}</p>
      </div>
    </div>
    <div class="p-6">
      @include('dashboard.karyawan._form', ['item' => $item])
    </div>
  </div>
@endsection
