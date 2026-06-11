<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class GeofencingController extends Controller
{
    public function index(): View
    {
        $items = DB::table('geofencing')
            ->orderByDesc('created_at')
            ->get();

        return view('dashboard.geofencing.index', [
            'items' => $items,
            'mapsApiKey' => (string) env('GOOGLE_MAPS_API_KEY', ''),
        ]);
    }

    public function create(): View
    {
        return view('dashboard.geofencing.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_lokasi' => ['required', 'string', 'max:120'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_meter' => ['required', 'integer', 'min:50', 'max:1000'],
            'aktif' => ['required', 'boolean'],
        ]);

        $adminId = $this->currentAdministratorId();

        DB::table('geofencing')->insert([
            'id' => (string) Str::uuid(),
            'nama_lokasi' => $validated['nama_lokasi'],
            'latitude' => $validated['latitude'],
            'longitude' => $validated['longitude'],
            'radius_meter' => $validated['radius_meter'],
            'aktif' => $validated['aktif'],
            'dibuat_oleh_admin_id' => $adminId,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('dashboard.geofencing.index')->with('success', 'Titik geofencing berhasil ditambahkan.');
    }

    public function edit(string $geofencing): View
    {
        $item = DB::table('geofencing')->where('id', $geofencing)->firstOrFail();

        return view('dashboard.geofencing.edit', ['item' => $item]);
    }

    public function update(Request $request, string $geofencing): RedirectResponse
    {
        $validated = $request->validate([
            'nama_lokasi' => ['required', 'string', 'max:120'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'radius_meter' => ['required', 'integer', 'min:50', 'max:1000'],
            'aktif' => ['required', 'boolean'],
        ]);

        DB::table('geofencing')
            ->where('id', $geofencing)
            ->update([
                'nama_lokasi' => $validated['nama_lokasi'],
                'latitude' => $validated['latitude'],
                'longitude' => $validated['longitude'],
                'radius_meter' => $validated['radius_meter'],
                'aktif' => $validated['aktif'],
                'updated_at' => now(),
            ]);

        return redirect()->route('dashboard.geofencing.index')->with('success', 'Titik geofencing berhasil diperbarui.');
    }

    public function toggleAktif(string $geofencing): RedirectResponse
    {
        $item = DB::table('geofencing')->where('id', $geofencing)->firstOrFail();

        DB::table('geofencing')
            ->where('id', $geofencing)
            ->update([
                'aktif' => !$item->aktif,
                'updated_at' => now(),
            ]);

        return redirect()->route('dashboard.geofencing.index')->with('success', 'Status geofencing berhasil diperbarui.');
    }

    public function destroy(string $geofencing): RedirectResponse
    {
        DB::table('geofencing')->where('id', $geofencing)->delete();

        return redirect()->route('dashboard.geofencing.index')->with('success', 'Titik geofencing berhasil dihapus.');
    }

    private function currentAdministratorId(): ?string
    {
        $penggunaId = auth()->id();
        if (!$penggunaId) {
            return null;
        }

        return DB::table('administrator')
            ->where('pengguna_id', $penggunaId)
            ->value('id');
    }
}
