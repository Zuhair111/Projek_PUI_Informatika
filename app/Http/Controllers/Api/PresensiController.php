<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Support\ApiResponse;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class PresensiController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $startAt = microtime(true);
        $requestId = (string) Str::uuid();
        $response = null;
        $tipePresensi = (string) $request->input('tipe_presensi', '');

        try {
            $validated = $request->validate([
                'id_karyawan' => ['nullable', 'uuid'],
                'id_geofencing' => ['required', 'uuid'],
                'tipe_presensi' => ['required', 'in:masuk,pulang'],
                'waktu' => ['required', 'date'],
                'latitude' => ['required', 'numeric', 'between:-90,90'],
                'longitude' => ['required', 'numeric', 'between:-180,180'],
                'status_presensi' => ['required', 'in:hadir,terlambat,izin,alpha,ditolak'],
            ]);

            $tipePresensi = $validated['tipe_presensi'];

            $user = $request->user();
            $karyawanId = $this->ensureKaryawanProfile($user->id);

            $geofence = DB::table('geofencing')
                ->select(['id', 'latitude', 'longitude', 'radius_meter'])
                ->where('id', $validated['id_geofencing'])
                ->where('aktif', true)
                ->first();

            if (!$geofence) {
                $response = ApiResponse::error('Titik geofencing tidak ditemukan atau tidak aktif.', [], 422);
                return $response;
            }

            $waktu = Carbon::parse($validated['waktu']);
            $tanggal = $waktu->toDateString();

            $existing = DB::table('presensi')
                ->where('karyawan_id', $karyawanId)
                ->where('tanggal', $tanggal)
                ->first();

            $jarak = $this->haversineDistanceMeters(
                (float) $validated['latitude'],
                (float) $validated['longitude'],
                (float) $geofence->latitude,
                (float) $geofence->longitude,
            );

            // Enforce geofence validation at API level to prevent direct API bypass.
            if ($jarak > (float) $geofence->radius_meter) {
                $response = ApiResponse::error('Koordinat berada di luar radius geofencing.', [
                    'jarak_meter' => round($jarak, 2),
                    'radius_meter' => (float) $geofence->radius_meter,
                ], 422);
                return $response;
            }

            if ($validated['tipe_presensi'] === 'masuk') {
                // Employee can only check in once per day.
                if ($existing && $existing->check_in_at) {
                    $response = ApiResponse::error('Presensi masuk sudah dilakukan hari ini.', [], 422);
                    return $response;
                }

                if (!$existing) {
                    $presensiId = (string) Str::uuid();

                    DB::table('presensi')->insert([
                        'id' => $presensiId,
                        'karyawan_id' => $karyawanId,
                        'geofencing_id' => $validated['id_geofencing'],
                        'tanggal' => $tanggal,
                        'check_in_at' => $waktu,
                        'lat_check_in' => $validated['latitude'],
                        'lng_check_in' => $validated['longitude'],
                        'jarak_dari_titik_meter' => $jarak,
                        'status' => $validated['status_presensi'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    DB::table('presensi')
                        ->where('id', $existing->id)
                        ->update([
                            'geofencing_id' => $validated['id_geofencing'],
                            'check_in_at' => $waktu,
                            'lat_check_in' => $validated['latitude'],
                            'lng_check_in' => $validated['longitude'],
                            'jarak_dari_titik_meter' => $jarak,
                            'status' => $validated['status_presensi'],
                            'updated_at' => now(),
                        ]);

                    $presensiId = $existing->id;
                }

                $response = ApiResponse::success('Presensi masuk berhasil disimpan.', ['id' => $presensiId], 201);
                return $response;
            }

            // Employee can only check out once per day and must check in first.
            if (!$existing || !$existing->check_in_at) {
                $response = ApiResponse::error('Presensi masuk belum dilakukan hari ini.', [], 422);
                return $response;
            }

            if ($existing->check_out_at) {
                $response = ApiResponse::error('Presensi pulang sudah dilakukan hari ini.', [], 422);
                return $response;
            }

            DB::table('presensi')
                ->where('id', $existing->id)
                ->update([
                    'check_out_at' => $waktu,
                    'lat_check_out' => $validated['latitude'],
                    'lng_check_out' => $validated['longitude'],
                    'status' => $validated['status_presensi'],
                    'updated_at' => now(),
                ]);

            $response = ApiResponse::success('Presensi pulang berhasil disimpan.', ['id' => $existing->id]);
            return $response;
        } finally {
            $latencyMs = (int) round((microtime(true) - $startAt) * 1000);
            $statusCode = $response instanceof JsonResponse ? $response->status() : null;
            $payload = $response instanceof JsonResponse ? $response->getData(true) : [];

            Log::channel('presensi_latency')->info('presensi_latency', [
                'request_id' => $requestId,
                'tipe_presensi' => $tipePresensi,
                'karyawan_id' => $request->user()?->id,
                'presensi_id' => $payload['data']['id'] ?? null,
                'status_code' => $statusCode,
                'latency_ms' => $latencyMs,
            ]);
        }
    }

    public function riwayat(Request $request): JsonResponse
    {
        $user = $request->user();
        $karyawanId = Karyawan::query()->where('pengguna_id', $user->id)->value('id');

        if (!$karyawanId) {
            return ApiResponse::error('Data karyawan tidak ditemukan untuk akun ini.', [], 422);
        }

        $data = DB::table('presensi as p')
            ->leftJoin('geofencing as g', 'g.id', '=', 'p.geofencing_id')
            ->where('p.karyawan_id', $karyawanId)
            ->orderByDesc('p.tanggal')
            ->orderByDesc('p.created_at')
            ->select([
                'p.id',
                'p.tanggal',
                'p.check_in_at',
                'p.check_out_at',
                'p.status',
                DB::raw("COALESCE(g.nama_lokasi, '-') as nama_lokasi"),
            ])
            ->get();

        return ApiResponse::success('Riwayat presensi berhasil diambil.', $data->toArray());
    }

    private function haversineDistanceMeters(
        float $startLat,
        float $startLng,
        float $endLat,
        float $endLng,
    ): float {
        $earthRadius = 6371000;

        $dLat = deg2rad($endLat - $startLat);
        $dLng = deg2rad($endLng - $startLng);

        $a = sin($dLat / 2) * sin($dLat / 2)
            + cos(deg2rad($startLat)) * cos(deg2rad($endLat))
            * sin($dLng / 2) * sin($dLng / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }

    private function ensureKaryawanProfile(string $penggunaId): string
    {
        $existingId = Karyawan::query()
            ->where('pengguna_id', $penggunaId)
            ->value('id');

        if ($existingId) {
            return $existingId;
        }

        do {
            $generatedNip = 'AUTO' . now()->format('ymdHis') . random_int(100, 999);
            $nipExists = Karyawan::query()->where('nip', $generatedNip)->exists();
        } while ($nipExists);

        $karyawan = Karyawan::query()->create([
            'id' => (string) Str::uuid(),
            'pengguna_id' => $penggunaId,
            'nip' => $generatedNip,
            'departemen' => null,
            'no_hp' => null,
            'jabatan' => null,
            'foto_url' => null,
        ]);

        return $karyawan->id;
    }
}
