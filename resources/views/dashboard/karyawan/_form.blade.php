@php $isEdit = isset($item); @endphp

<form method="POST" action="{{ $isEdit ? route('dashboard.karyawan.update', $item->id) : route('dashboard.karyawan.store') }}" class="space-y-5">
  @csrf
  @if ($isEdit) @method('PUT') @endif

  <div class="grid gap-4 sm:grid-cols-2">
    <div>
      <label class="mb-1.5 block text-sm font-medium text-gray-700">Nama Lengkap</label>
      <input name="nama" value="{{ old('nama', $item->nama ?? '') }}" required
        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 outline-none transition-all placeholder:text-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
    </div>
    <div>
      <label class="mb-1.5 block text-sm font-medium text-gray-700">Alamat Email</label>
      <input type="email" name="email" value="{{ old('email', $item->email ?? '') }}" required
        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 outline-none transition-all placeholder:text-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
    </div>
    <div>
      <label class="mb-1.5 block text-sm font-medium text-gray-700">
        Password
        @if($isEdit) <span class="font-normal text-gray-400">(opsional)</span> @endif
      </label>
      <input type="password" name="password" {{ $isEdit ? '' : 'required' }}
        placeholder="{{ $isEdit ? 'Kosongkan jika tidak diubah' : 'Min. 8 karakter' }}"
        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 outline-none transition-all placeholder:text-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
    </div>
    <div>
      <label class="mb-1.5 block text-sm font-medium text-gray-700">NIP</label>
      <input name="nip" value="{{ old('nip', $item->nip ?? '') }}" required
        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 outline-none transition-all placeholder:text-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
    </div>
    <div>
      <label class="mb-1.5 block text-sm font-medium text-gray-700">Departemen</label>
      <input name="departemen" value="{{ old('departemen', $item->departemen ?? '') }}" required
        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 outline-none transition-all placeholder:text-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
    </div>
    <div>
      <label class="mb-1.5 block text-sm font-medium text-gray-700">Jabatan</label>
      <input name="jabatan" value="{{ old('jabatan', $item->jabatan ?? '') }}" required
        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 outline-none transition-all placeholder:text-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
    </div>
    <div>
      <label class="mb-1.5 block text-sm font-medium text-gray-700">Nomor Telepon</label>
      <input name="no_hp" value="{{ old('no_hp', $item->no_hp ?? '') }}"
        placeholder="Opsional"
        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 outline-none transition-all placeholder:text-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
    </div>
    <div>
      <label class="mb-1.5 block text-sm font-medium text-gray-700">Status Aktif</label>
      <select name="is_active" required
        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 outline-none transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
        <option value="1" {{ old('is_active', (int) ($item->is_active ?? 1)) === 1 ? 'selected' : '' }}>Aktif</option>
        <option value="0" {{ old('is_active', (int) ($item->is_active ?? 1)) === 0 ? 'selected' : '' }}>Nonaktif</option>
      </select>
    </div>
  </div>

  <div class="flex items-center gap-3 border-t border-gray-100 pt-5">
    <button type="submit"
      class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-indigo-700 hover:shadow">
      <i data-lucide="save" class="h-4 w-4"></i>
      {{ $isEdit ? 'Simpan Perubahan' : 'Tambah Karyawan' }}
    </button>
    <a href="{{ route('dashboard.karyawan.index') }}"
      class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-600 transition-all hover:border-gray-300 hover:bg-gray-50 hover:text-gray-800">
      Batal
    </a>
  </div>
</form>
