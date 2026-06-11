@extends('dashboard.layouts.app', ['title' => 'Tambah Karyawan'])

@section('content')
  <div class="mb-6">
    <a href="{{ route('dashboard.karyawan.index') }}"
      class="inline-flex items-center gap-1.5 text-sm font-medium text-gray-500 transition-colors hover:text-gray-700">
      <i data-lucide="arrow-left" class="h-4 w-4"></i>
      Kembali ke Daftar Karyawan
    </a>
  </div>

  <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
    <div class="border-b border-gray-100 px-6 py-5">
      <h2 class="text-base font-bold text-gray-900">Tambah Karyawan Baru</h2>
      <p class="mt-0.5 text-sm text-gray-400">Isi seluruh data yang diperlukan untuk mendaftarkan karyawan.</p>
    </div>
    <div class="p-6">
      @include('dashboard.karyawan._form')
    </div>
  </div>
@endsection
