@extends('dashboard.layouts.app', ['title' => 'Atur Titik Geofencing'])

@section('content')
  {{-- ─── Header ──────────────────────────────────────── --}}
  <div class="mb-6">
    <h2 class="text-xl font-bold text-gray-900">Manajemen Geofencing</h2>
    <p class="mt-0.5 text-sm text-gray-400">Klik peta atau cari lokasi untuk menentukan titik pusat area presensi.</p>
  </div>

  <div class="space-y-5">
    {{-- ─── Map Card ──────────────────────────────────── --}}
    <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
      <div class="border-b border-gray-100 px-5 py-4">
        <p class="text-sm font-bold text-gray-900">Peta Lokasi</p>
      </div>
      <div class="p-4">
        <div class="mb-3 grid gap-2.5 md:grid-cols-[1fr_auto]">
          <input id="place-search" type="text"
            placeholder="Cari lokasi, contoh: Kantor Pusat Jakarta"
            class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 outline-none transition-all placeholder:text-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
          <button id="btn-use-my-location" type="button"
            class="inline-flex items-center gap-2 rounded-xl border border-indigo-200 bg-indigo-50 px-4 py-2.5 text-sm font-semibold text-indigo-700 transition-colors hover:bg-indigo-100 whitespace-nowrap">
            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="3"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/></svg>
            Gunakan Lokasi Saya
          </button>
        </div>
        <div id="map" class="h-[480px] w-full overflow-hidden rounded-xl border border-gray-200"></div>
      </div>
    </div>

    {{-- ─── Bottom: Form + List ────────────────────────── --}}
    <div class="grid gap-5 lg:grid-cols-[1.1fr_1fr]">

      {{-- Form Card --}}
      <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
        <div class="border-b border-gray-100 px-5 py-4">
          <p class="text-sm font-bold text-gray-900">Tambah Titik Geofencing</p>
        </div>
        <div class="p-5">
          <form method="POST" action="{{ route('dashboard.geofencing.store') }}" class="space-y-4">
            @csrf

            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700">Nama Lokasi</label>
              <input name="nama_lokasi" value="{{ old('nama_lokasi') }}" required
                placeholder="cth: Kantor Pusat"
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 outline-none transition-all placeholder:text-gray-400 focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
            </div>

            <div>
              <div class="mb-1.5 flex items-center justify-between">
                <label for="radius_meter" class="text-sm font-medium text-gray-700">Radius Geofencing</label>
                <span id="radius-label" class="rounded-lg bg-indigo-50 px-2.5 py-1 text-xs font-bold text-indigo-600">200 m</span>
              </div>
              <div class="space-y-2.5">
                <input id="radius_slider" type="range" min="50" max="1000"
                  value="{{ old('radius_meter', 200) }}"
                  class="w-full accent-indigo-600">
                <input id="radius_meter" type="number" min="50" max="1000" name="radius_meter"
                  value="{{ old('radius_meter', 200) }}" required
                  class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 outline-none transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
              </div>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Latitude</label>
                <input id="latitude" name="latitude" value="{{ old('latitude', '-6.2088') }}" required
                  class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 font-mono text-sm text-gray-900 outline-none transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
              </div>
              <div>
                <label class="mb-1.5 block text-sm font-medium text-gray-700">Longitude</label>
                <input id="longitude" name="longitude" value="{{ old('longitude', '106.8456') }}" required
                  class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 font-mono text-sm text-gray-900 outline-none transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
              </div>
            </div>

            <div>
              <label class="mb-1.5 block text-sm font-medium text-gray-700">Status</label>
              <select name="aktif" required
                class="w-full rounded-xl border border-gray-200 bg-gray-50 px-4 py-2.5 text-sm text-gray-900 outline-none transition-all focus:border-indigo-500 focus:bg-white focus:ring-4 focus:ring-indigo-500/10">
                <option value="1" selected>Aktif</option>
                <option value="0">Nonaktif</option>
              </select>
            </div>

            <div class="pt-1">
              <button type="submit"
                class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all hover:bg-indigo-700 hover:shadow">
                <i data-lucide="map-pin-plus" class="h-4 w-4"></i>
                Simpan Titik Geofencing
              </button>
            </div>
          </form>
        </div>
      </div>

      {{-- Saved Geofences List --}}
      <div class="overflow-hidden rounded-2xl border border-gray-100 bg-white shadow-sm">
        <div class="flex items-center justify-between border-b border-gray-100 px-5 py-4">
          <p class="text-sm font-bold text-gray-900">Titik Tersimpan</p>
          <span class="rounded-full bg-indigo-50 px-2.5 py-0.5 text-xs font-bold text-indigo-600">
            {{ $items->count() }}
          </span>
        </div>
        <div class="max-h-[520px] divide-y divide-gray-50 overflow-y-auto">
          @forelse ($items as $item)
            <div class="p-4 transition-colors hover:bg-gray-50/60">
              <div class="mb-3 flex items-start justify-between gap-2">
                <div>
                  <p class="text-sm font-semibold text-gray-900">{{ $item->nama_lokasi }}</p>
                  <p class="mt-0.5 font-mono text-[11px] text-gray-400">{{ $item->latitude }}, {{ $item->longitude }}</p>
                  <p class="mt-1 text-xs text-gray-500">Radius: <span class="font-semibold text-gray-700">{{ $item->radius_meter }} m</span></p>
                </div>
                <span class="shrink-0 inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-[11px] font-semibold
                  {{ $item->aktif ? 'bg-emerald-50 text-emerald-700 border border-emerald-200/60' : 'bg-gray-100 text-gray-500' }}">
                  <span class="h-1.5 w-1.5 rounded-full {{ $item->aktif ? 'bg-emerald-500' : 'bg-gray-400' }}"></span>
                  {{ $item->aktif ? 'Aktif' : 'Nonaktif' }}
                </span>
              </div>

              <div class="flex flex-wrap gap-1.5">
                <button type="button"
                  class="inline-flex items-center gap-1 rounded-lg border border-gray-200 bg-white px-2.5 py-1 text-xs font-medium text-gray-600 transition-colors hover:border-indigo-200 hover:bg-indigo-50 hover:text-indigo-700"
                  data-center-lat="{{ $item->latitude }}"
                  data-center-lng="{{ $item->longitude }}"
                  onclick="focusExistingGeofence(this)">
                  <svg xmlns="http://www.w3.org/2000/svg" width="11" height="11" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/></svg>
                  Lihat di Peta
                </button>

                <form method="POST" action="{{ route('dashboard.geofencing.toggle-aktif', $item->id) }}" class="inline">
                  @csrf @method('PATCH')
                  <button type="submit"
                    class="inline-flex items-center gap-1 rounded-lg px-2.5 py-1 text-xs font-medium transition-colors
                    {{ $item->aktif
                      ? 'bg-amber-50 text-amber-700 hover:bg-amber-100'
                      : 'bg-emerald-50 text-emerald-700 hover:bg-emerald-100' }}">
                    {{ $item->aktif ? 'Nonaktifkan' : 'Aktifkan' }}
                  </button>
                </form>

                <a href="{{ route('dashboard.geofencing.edit', $item->id) }}"
                  class="inline-flex items-center gap-1 rounded-lg bg-blue-50 px-2.5 py-1 text-xs font-medium text-blue-700 transition-colors hover:bg-blue-100">
                  Edit
                </a>

                <form method="POST" action="{{ route('dashboard.geofencing.destroy', $item->id) }}"
                  onsubmit="return confirm('Hapus titik geofencing ini?')" class="inline">
                  @csrf @method('DELETE')
                  <button type="submit"
                    class="inline-flex items-center gap-1 rounded-lg bg-rose-50 px-2.5 py-1 text-xs font-medium text-rose-700 transition-colors hover:bg-rose-100">
                    Hapus
                  </button>
                </form>
              </div>
            </div>
          @empty
            <div class="flex flex-col items-center justify-center px-5 py-12 text-center">
              <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-gray-100 text-gray-400">
                <i data-lucide="map-pin" class="h-6 w-6"></i>
              </div>
              <p class="mt-3 text-sm font-semibold text-gray-700">Belum ada titik geofencing</p>
              <p class="mt-1 text-xs text-gray-400">Klik pada peta dan isi formulir di sebelah kiri.</p>
            </div>
          @endforelse
        </div>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  @php
    $existingGeofences = $items->values()->map(function ($item) {
        return [
            'id'           => $item->id,
            'nama_lokasi'  => $item->nama_lokasi,
            'latitude'     => (float) $item->latitude,
            'longitude'    => (float) $item->longitude,
            'radius_meter' => (int) $item->radius_meter,
            'aktif'        => (bool) $item->aktif,
        ];
    })->all();
  @endphp

  @if (!empty($mapsApiKey))
    <script src="https://maps.googleapis.com/maps/api/js?key={{ $mapsApiKey }}&libraries=places"></script>
  @endif

  <script>
    const existingGeofences = @json($existingGeofences);

    const latInput      = document.getElementById('latitude');
    const lngInput      = document.getElementById('longitude');
    const radiusInput   = document.getElementById('radius_meter');
    const radiusSlider  = document.getElementById('radius_slider');
    const radiusLabel   = document.getElementById('radius-label');
    const searchInput   = document.getElementById('place-search');
    const useMyLocationButton = document.getElementById('btn-use-my-location');

    const initialCenter = {
      lat: parseFloat(latInput.value || '-6.2088'),
      lng: parseFloat(lngInput.value || '106.8456'),
    };

    function syncRadius(value) {
      const clamped = Math.max(50, Math.min(1000, Number(value) || 200));
      radiusInput.value  = clamped;
      radiusSlider.value = clamped;
      radiusLabel.textContent = `${clamped} m`;
      return clamped;
    }

    function normalizeLatLng(latLng) {
      latInput.value = latLng.lat().toFixed(7);
      lngInput.value = latLng.lng().toFixed(7);
    }

    function createColor(index) {
      const palette = ['#0284c7', '#16a34a', '#a21caf', '#ca8a04', '#dc2626', '#2563eb'];
      return palette[index % palette.length];
    }

    if (typeof google === 'undefined' || !google.maps) {
      document.getElementById('map').innerHTML =
        '<div class="flex h-full items-center justify-center text-sm text-rose-600 font-medium">' +
        'Google Maps API key belum disetel. Tambahkan GOOGLE_MAPS_API_KEY pada .env.</div>';
    } else {
      const map = new google.maps.Map(document.getElementById('map'), {
        center: initialCenter,
        zoom: 15,
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControlOptions: { position: google.maps.ControlPosition.RIGHT_BOTTOM },
      });

      const draftMarker = new google.maps.Marker({
        map,
        position: initialCenter,
        draggable: true,
        title: 'Titik geofencing baru',
      });

      const draftCircle = new google.maps.Circle({
        map,
        center: initialCenter,
        radius: syncRadius(radiusInput.value),
        strokeColor: '#6366f1',
        strokeOpacity: 0.9,
        strokeWeight: 2,
        fillColor: '#6366f1',
        fillOpacity: 0.12,
      });

      existingGeofences.forEach((item, index) => {
        const color    = createColor(index);
        const position = { lat: Number(item.latitude), lng: Number(item.longitude) };

        new google.maps.Marker({
          map,
          position,
          title: item.nama_lokasi,
          icon: {
            path: google.maps.SymbolPath.CIRCLE,
            fillColor: color,
            fillOpacity: 1,
            strokeColor: '#ffffff',
            strokeWeight: 2,
            scale: 8,
          },
          label: { text: `${index + 1}`, color: '#ffffff', fontSize: '10px' },
        });

        new google.maps.Circle({
          map,
          center: position,
          radius: Number(item.radius_meter),
          strokeColor: color,
          strokeOpacity: item.aktif ? 0.9 : 0.4,
          strokeWeight: 2,
          fillColor: color,
          fillOpacity: item.aktif ? 0.14 : 0.05,
        });
      });

      function moveDraftTo(latLng) {
        draftMarker.setPosition(latLng);
        draftCircle.setCenter(latLng);
        normalizeLatLng(latLng);
      }

      map.addListener('click', (event) => moveDraftTo(event.latLng));
      draftMarker.addListener('dragend', (event) => moveDraftTo(event.latLng));

      const searchBox = new google.maps.places.SearchBox(searchInput);
      searchBox.addListener('places_changed', () => {
        const places = searchBox.getPlaces();
        if (!places || places.length === 0) return;
        const place = places[0];
        if (!place.geometry || !place.geometry.location) return;
        map.panTo(place.geometry.location);
        map.setZoom(17);
        moveDraftTo(place.geometry.location);
      });

      map.addListener('bounds_changed', () => searchBox.setBounds(map.getBounds()));

      radiusInput.addEventListener('input', () => {
        draftCircle.setRadius(syncRadius(radiusInput.value));
      });
      radiusSlider.addEventListener('input', () => {
        draftCircle.setRadius(syncRadius(radiusSlider.value));
      });

      useMyLocationButton.addEventListener('click', () => {
        if (!navigator.geolocation) return;
        navigator.geolocation.getCurrentPosition((pos) => {
          const latLng = new google.maps.LatLng(pos.coords.latitude, pos.coords.longitude);
          map.panTo(latLng);
          map.setZoom(17);
          moveDraftTo(latLng);
        });
      });

      window.focusExistingGeofence = (button) => {
        const lat = Number(button.dataset.centerLat);
        const lng = Number(button.dataset.centerLng);
        if (Number.isNaN(lat) || Number.isNaN(lng)) return;
        const latLng = new google.maps.LatLng(lat, lng);
        map.panTo(latLng);
        map.setZoom(17);
      };
    }

    syncRadius(radiusInput.value);
  </script>
@endpush
