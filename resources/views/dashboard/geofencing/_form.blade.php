@php
  $isEdit     = isset($item);
  $defaultLat = old('latitude',  $item->latitude  ?? '-6.2088');
  $defaultLng = old('longitude', $item->longitude ?? '106.8456');
@endphp

<div class="mb-4">
  <div id="map" class="h-72 w-full overflow-hidden rounded-xl border border-gray-200"></div>
  <p class="mt-2 text-xs text-gray-400">Klik peta atau drag marker untuk memilih titik pusat geofencing.</p>
</div>

<form method="POST" action="{{ $isEdit ? route('dashboard.geofencing.update', $item->id) : route('dashboard.geofencing.store') }}" class="space-y-4">
  @csrf
  @if ($isEdit) @method('PUT') @endif

  <div class="grid gap-4 sm:grid-cols-2">
    <div>
      <label class="mb-1.5 block text-sm font-medium text-gray-700">Nama Lokasi</label>
      <input name="nama_lokasi" value="{{ old('nama_lokasi', $item->nama_lokasi ?? '') }}" required
        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 outline-none transition-all placeholder:text-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
    </div>
    <div>
      <label class="mb-1.5 block text-sm font-medium text-gray-700">Radius (meter)</label>
      <input type="number" min="1" name="radius_meter"
        value="{{ old('radius_meter', $item->radius_meter ?? 100) }}" required
        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 outline-none transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
    </div>
    <div>
      <label class="mb-1.5 block text-sm font-medium text-gray-700">Latitude</label>
      <input id="latitude" name="latitude" value="{{ $defaultLat }}" required
        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 font-mono text-sm text-gray-900 outline-none transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
    </div>
    <div>
      <label class="mb-1.5 block text-sm font-medium text-gray-700">Longitude</label>
      <input id="longitude" name="longitude" value="{{ $defaultLng }}" required
        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 font-mono text-sm text-gray-900 outline-none transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
    </div>
    <div class="sm:col-span-2">
      <label class="mb-1.5 block text-sm font-medium text-gray-700">Status</label>
      <select name="aktif" required
        class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 outline-none transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
        <option value="1" {{ old('aktif', (int) ($item->aktif ?? 1)) === 1 ? 'selected' : '' }}>Aktif</option>
        <option value="0" {{ old('aktif', (int) ($item->aktif ?? 1)) === 0 ? 'selected' : '' }}>Nonaktif</option>
      </select>
    </div>
  </div>

  <div class="flex items-center gap-3 border-t border-gray-100 pt-4">
    <button type="submit"
      class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-indigo-700 hover:shadow">
      <i data-lucide="save" class="h-4 w-4"></i>
      {{ $isEdit ? 'Simpan Perubahan' : 'Simpan Geofencing' }}
    </button>
    <a href="{{ route('dashboard.geofencing.index') }}"
      class="inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-5 py-2.5 text-sm font-medium text-gray-600 transition-all hover:border-gray-300 hover:bg-gray-50">
      Batal
    </a>
  </div>
</form>

@push('scripts')
  <script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_API_KEY') }}"></script>
  <script>
    const latInput = document.getElementById('latitude');
    const lngInput = document.getElementById('longitude');

    const initialPos = {
      lat: parseFloat(latInput.value || '-6.2088'),
      lng: parseFloat(lngInput.value || '106.8456'),
    };

    const map = new google.maps.Map(document.getElementById('map'), {
      center: initialPos,
      zoom: 15,
      mapTypeControl: false,
      streetViewControl: false,
    });

    const marker = new google.maps.Marker({
      position: initialPos,
      map,
      draggable: true,
    });

    const syncInputs = (pos) => {
      latInput.value = pos.lat().toFixed(7);
      lngInput.value = pos.lng().toFixed(7);
    };

    map.addListener('click', (event) => {
      marker.setPosition(event.latLng);
      syncInputs(event.latLng);
    });

    marker.addListener('dragend', (event) => syncInputs(event.latLng));
  </script>
@endpush
