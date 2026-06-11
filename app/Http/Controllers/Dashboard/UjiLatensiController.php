<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class UjiLatensiController extends Controller
{
    private const TEST_EMAIL_SUFFIX = '@test.local';

    public function index()
    {
        return view('dashboard.uji-latensi.index');
    }

    // ── Proxy: geofencing aktif (read-only) ──────────────────
    public function proxyGeofencing(): JsonResponse
    {
        $data = DB::table('geofencing')
            ->select(['id', 'nama_lokasi', 'latitude', 'longitude', 'radius_meter'])
            ->where('aktif', true)
            ->orderBy('nama_lokasi')
            ->get();

        return response()->json(['success' => true, 'data' => $data->toArray()]);
    }

    // ── Proxy: presensi masuk / pulang (rollback) ────────────
    public function proxyPresensi(Request $request): JsonResponse
    {
        $startAt = microtime(true);

        $validated = $request->validate([
            'tipe_presensi'   => ['required', 'in:masuk,pulang'],
            'id_geofencing'   => ['required', 'uuid'],
            'waktu'           => ['required', 'date'],
            'latitude'        => ['required', 'numeric', 'between:-90,90'],
            'longitude'       => ['required', 'numeric', 'between:-180,180'],
            'status_presensi' => ['required', 'in:hadir,terlambat,izin,alpha,ditolak'],
        ]);

        $geofence = DB::table('geofencing')
            ->select(['id', 'latitude', 'longitude', 'radius_meter'])
            ->where('id', $validated['id_geofencing'])
            ->where('aktif', true)
            ->first();

        if (!$geofence) {
            return response()->json([
                'ok' => false, 'latency_ms' => (int) round((microtime(true) - $startAt) * 1000),
                'status_code' => 422, 'message' => 'Geofencing tidak ditemukan atau tidak aktif',
            ]);
        }

        $jarak = $this->haversine(
            (float) $validated['latitude'], (float) $validated['longitude'],
            (float) $geofence->latitude,    (float) $geofence->longitude,
        );

        if ($jarak > (float) $geofence->radius_meter) {
            return response()->json([
                'ok' => false, 'latency_ms' => (int) round((microtime(true) - $startAt) * 1000),
                'status_code' => 422, 'message' => 'Koordinat berada di luar radius geofencing',
                'jarak_meter' => round($jarak, 2),
            ]);
        }

        $waktu   = Carbon::parse($validated['waktu']);
        $tanggal = $waktu->toDateString();

        DB::beginTransaction();
        try {
            $fakePenggunaId = (string) Str::uuid();
            $fakeKaryawanId = (string) Str::uuid();
            $fakePresensiId = (string) Str::uuid();

            DB::table('pengguna')->insert([
                'id' => $fakePenggunaId, 'nama' => 'UJI_PROXY',
                'email' => 'proxy_' . $fakePenggunaId . '@proxy.internal',
                'password_hash' => 'proxy_hash_not_real', 'is_active' => true,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('karyawan')->insert([
                'id' => $fakeKaryawanId, 'pengguna_id' => $fakePenggunaId,
                'nip' => 'UJI' . now()->format('ymdHis') . random_int(100, 999),
                'created_at' => now(), 'updated_at' => now(),
            ]);

            if ($validated['tipe_presensi'] === 'masuk') {
                DB::table('presensi')->insert([
                    'id' => $fakePresensiId, 'karyawan_id' => $fakeKaryawanId,
                    'geofencing_id' => $validated['id_geofencing'], 'tanggal' => $tanggal,
                    'check_in_at' => $waktu, 'lat_check_in' => $validated['latitude'],
                    'lng_check_in' => $validated['longitude'], 'jarak_dari_titik_meter' => $jarak,
                    'status' => $validated['status_presensi'], 'created_at' => now(), 'updated_at' => now(),
                ]);
            } else {
                DB::table('presensi')->insert([
                    'id' => $fakePresensiId, 'karyawan_id' => $fakeKaryawanId,
                    'geofencing_id' => $validated['id_geofencing'], 'tanggal' => $tanggal,
                    'check_in_at' => $waktu->copy()->subHours(8), 'lat_check_in' => $validated['latitude'],
                    'lng_check_in' => $validated['longitude'], 'jarak_dari_titik_meter' => $jarak,
                    'status' => 'hadir', 'created_at' => now(), 'updated_at' => now(),
                ]);
                DB::table('presensi')->where('id', $fakePresensiId)->update([
                    'check_out_at' => $waktu, 'lat_check_out' => $validated['latitude'],
                    'lng_check_out' => $validated['longitude'], 'status' => $validated['status_presensi'],
                    'updated_at' => now(),
                ]);
            }

            DB::rollBack();
            return response()->json([
                'ok' => true, 'latency_ms' => (int) round((microtime(true) - $startAt) * 1000), 'status_code' => 201,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'ok' => false, 'latency_ms' => (int) round((microtime(true) - $startAt) * 1000),
                'status_code' => 500, 'message' => $e->getMessage(),
            ]);
        }
    }

    // ── Proxy: log deteksi mock location (rollback) ──────────
    public function proxyLogDeteksi(Request $request): JsonResponse
    {
        $startAt = microtime(true);

        $validated = $request->validate([
            'mock_location'  => ['required', 'boolean'],
            'is_real_device' => ['required', 'boolean'],
            'is_rooted'      => ['required', 'boolean'],
            'is_dev_mode'    => ['required', 'boolean'],
            'final_status'   => ['required', 'in:HARD_BLOCK_MOCK,HARD_BLOCK_EMULATOR,HARD_BLOCK_SYNERGISTIC_THREAT,HARD_BLOCK_SENSOR_FAILED,SOFT_RISK,SAFE'],
            'message'        => ['nullable', 'string'],
        ]);

        DB::beginTransaction();
        try {
            $fakePenggunaId = (string) Str::uuid();
            $fakeKaryawanId = (string) Str::uuid();

            DB::table('pengguna')->insert([
                'id' => $fakePenggunaId, 'nama' => 'UJI_PROXY',
                'email' => 'proxy_' . $fakePenggunaId . '@proxy.internal',
                'password_hash' => 'proxy_hash_not_real', 'is_active' => true,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('karyawan')->insert([
                'id' => $fakeKaryawanId, 'pengguna_id' => $fakePenggunaId,
                'nip' => 'UJI' . now()->format('ymdHis') . random_int(100, 999),
                'created_at' => now(), 'updated_at' => now(),
            ]);
            DB::table('log_deteksi')->insert([
                'id' => (string) Str::uuid(), 'karyawan_id' => $fakeKaryawanId, 'presensi_id' => null,
                'mock_location' => $validated['mock_location'], 'is_real_device' => $validated['is_real_device'],
                'is_rooted' => $validated['is_rooted'], 'is_dev_mode' => $validated['is_dev_mode'],
                'final_status' => $validated['final_status'], 'message' => $validated['message'] ?? null,
                'detected_at' => now(), 'created_at' => now(),
            ]);

            DB::rollBack();
            return response()->json([
                'ok' => true, 'latency_ms' => (int) round((microtime(true) - $startAt) * 1000), 'status_code' => 201,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'ok' => false, 'latency_ms' => (int) round((microtime(true) - $startAt) * 1000),
                'status_code' => 500, 'message' => $e->getMessage(),
            ]);
        }
    }

    // ── Real data: latensi dari presensi_latency.log ─────────
    public function realLatensi(): JsonResponse
    {
        $logPath = storage_path('logs/presensi_latency.log');

        if (!file_exists($logPath)) {
            return response()->json(['error' => 'File presensi_latency.log belum ada. Lakukan presensi dari mobile terlebih dahulu.']);
        }

        $lines = file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [];

        $masukLat = $pulangLat = [];
        $masukGagal = $pulangGagal = 0;

        foreach ($lines as $line) {
            if (!preg_match('/presensi_latency (\{.+\})/', $line, $m)) {
                continue;
            }
            $e = json_decode($m[1], true);
            if (!$e || !isset($e['latency_ms'])) {
                continue;
            }

            $tipe = $e['tipe_presensi'] ?? '';
            $sc   = (int) ($e['status_code'] ?? 0);
            $ms   = (int) ($e['latency_ms']  ?? 0);

            if ($tipe === 'masuk') {
                $sc === 201 ? ($masukLat[] = $ms) : $masukGagal++;
            } elseif ($tipe === 'pulang') {
                ($sc === 200 || $sc === 201) ? ($pulangLat[] = $ms) : $pulangGagal++;
            }
        }

        return response()->json([
            'masuk'         => ['latencies' => $masukLat,  'statistik' => $this->statistik($masukLat),  'gagal' => $masukGagal],
            'pulang'        => ['latencies' => $pulangLat, 'statistik' => $this->statistik($pulangLat), 'gagal' => $pulangGagal],
            'total_entries' => count($lines),
        ]);
    }

    // ── Real data: akurasi geofence dari DB + log ────────────
    public function realGeofence(): JsonResponse
    {
        // Presensi tersimpan = semua lolos geofencing (dalam radius)
        $rows = DB::table('presensi as p')
            ->join('geofencing as g', 'g.id', '=', 'p.geofencing_id')
            ->whereNotNull('p.check_in_at')
            ->whereNotNull('p.jarak_dari_titik_meter')
            ->select(['p.jarak_dari_titik_meter', 'g.radius_meter', 'g.nama_lokasi'])
            ->orderByDesc('p.check_in_at')
            ->limit(500)
            ->get();

        $jarakList = $rows->map(fn($r) => (float) $r->jarak_dari_titik_meter)->values()->toArray();

        // Presensi ditolak dari log (422 tipe masuk)
        $logPath  = storage_path('logs/presensi_latency.log');
        $ditolak  = [];
        $tolakMs  = [];
        if (file_exists($logPath)) {
            foreach (file($logPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
                if (!preg_match('/presensi_latency (\{.+\})/', $line, $m)) {
                    continue;
                }
                $e = json_decode($m[1], true);
                if ($e && ($e['tipe_presensi'] ?? '') === 'masuk' && (int) ($e['status_code'] ?? 0) === 422) {
                    $tolakMs[] = (int) ($e['latency_ms'] ?? 0);
                    $ditolak[] = $e;
                }
            }
        }

        return response()->json([
            'dalam_radius' => [
                'count'           => count($jarakList),
                'jarak_list_meter'=> $jarakList,
                'statistik_jarak' => $this->statistik(array_map('intval', $jarakList)),
                'akurasi'         => count($jarakList) > 0 ? 100.0 : null,
            ],
            'ditolak_422' => [
                'count'    => count($ditolak),
                'latencies'=> $tolakMs,
                'catatan'  => 'Termasuk penolakan "sudah presensi hari ini" — bukan hanya geofencing',
            ],
        ]);
    }

    // ── Real data: deteksi mock dari log_deteksi ──────────────
    public function realMock(): JsonResponse
    {
        $logs = DB::table('log_deteksi')
            ->select(['mock_location', 'is_real_device', 'is_rooted', 'is_dev_mode', 'final_status', 'detected_at'])
            ->orderByDesc('detected_at')
            ->limit(500)
            ->get();

        if ($logs->isEmpty()) {
            return response()->json(['error' => 'Belum ada data log_deteksi. Lakukan presensi dari mobile (dengan deteksi mock) terlebih dahulu.']);
        }

        $grouped = [];
        $scenarioMeta = [
            'Tahap 1'     => ['no' => 1, 'skenario' => 'mockLocationDetected aktif',                        'expected' => 'HARD_BLOCK_MOCK'],
            'Tahap 2'     => ['no' => 2, 'skenario' => 'isRealDevice = false (emulator)',                   'expected' => 'HARD_BLOCK_EMULATOR'],
            'Tahap 3'     => ['no' => 3, 'skenario' => 'rootedDetected + developmentModeEnabled bersamaan', 'expected' => 'HARD_BLOCK_SYNERGISTIC_THREAT'],
            'Tahap 4a'    => ['no' => 4, 'skenario' => 'rootedDetected aktif saja',                        'expected' => 'SOFT_RISK'],
            'Tahap 4b'    => ['no' => 5, 'skenario' => 'developmentModeEnabled aktif saja',                'expected' => 'SOFT_RISK'],
            'Tahap 5'     => ['no' => 6, 'skenario' => 'Semua sinyal aman, perangkat standar pabrikan',    'expected' => 'SAFE'],
            'Fail-Closed' => ['no' => 7, 'skenario' => 'Pustaka Safe Device error/crash',                  'expected' => 'HARD_BLOCK_SENSOR_FAILED'],
        ];

        foreach ($logs as $log) {
            $mock  = (bool) $log->mock_location;
            $real  = (bool) $log->is_real_device;
            $root  = (bool) $log->is_rooted;
            $dev   = (bool) $log->is_dev_mode;
            $final = $log->final_status;

            if ($mock) {
                $tahap    = 'Tahap 1';
                $expected = 'HARD_BLOCK_MOCK';
            } elseif (!$real) {
                $tahap    = 'Tahap 2';
                $expected = 'HARD_BLOCK_EMULATOR';
            } elseif ($root && $dev) {
                $tahap    = 'Tahap 3';
                $expected = 'HARD_BLOCK_SYNERGISTIC_THREAT';
            } elseif ($root) {
                $tahap    = 'Tahap 4a';
                $expected = 'SOFT_RISK';
            } elseif ($dev) {
                $tahap    = 'Tahap 4b';
                $expected = 'SOFT_RISK';
            } elseif ($final === 'HARD_BLOCK_SENSOR_FAILED') {
                $tahap    = 'Fail-Closed';
                $expected = 'HARD_BLOCK_SENSOR_FAILED';
            } else {
                $tahap    = 'Tahap 5';
                $expected = 'SAFE';
            }

            $grouped[$tahap]['total']   = ($grouped[$tahap]['total']   ?? 0) + 1;
            $grouped[$tahap]['sesuai']  = ($grouped[$tahap]['sesuai']  ?? 0) + ($final === $expected ? 1 : 0);
            $grouped[$tahap]['expected']= $expected;
        }

        $result = [];
        foreach ($scenarioMeta as $tahap => $meta) {
            $g      = $grouped[$tahap] ?? ['total' => 0, 'sesuai' => 0, 'expected' => $meta['expected']];
            $total  = $g['total'];
            $sesuai = $g['sesuai'];
            $result[] = [
                'no'          => $meta['no'],
                'tahap'       => $tahap,
                'skenario'    => $meta['skenario'],
                'expected'    => $meta['expected'],
                'total'       => $total,
                'sesuai'      => $sesuai,
                'tidak_sesuai'=> $total - $sesuai,
                'akurasi'     => $total > 0 ? round($sesuai / $total * 100, 2) : null,
            ];
        }

        return response()->json([
            'total_logs'  => $logs->count(),
            'scenarios'   => $result,
            'per_status'  => $logs->groupBy('final_status')->map->count(),
        ]);
    }

    // ── Hapus data uji lama ───────────────────────────────────
    public function bersihkan(): JsonResponse
    {
        $penggunaIds = DB::table('pengguna')
            ->where('email', 'like', '%' . self::TEST_EMAIL_SUFFIX)
            ->pluck('id');

        if ($penggunaIds->isEmpty()) {
            return response()->json(['pesan' => 'Tidak ada data uji lama untuk dihapus.']);
        }

        $karyawanIds = DB::table('karyawan')->whereIn('pengguna_id', $penggunaIds)->pluck('id');
        $hapusPresensi = $karyawanIds->isNotEmpty()
            ? DB::table('presensi')->whereIn('karyawan_id', $karyawanIds)->delete() : 0;
        $hapusToken = DB::table('personal_access_tokens')
            ->where('tokenable_type', 'App\\Models\\Pengguna')
            ->whereIn('tokenable_id', $penggunaIds)->delete();
        $hapusPengguna = DB::table('pengguna')->whereIn('id', $penggunaIds)->delete();

        return response()->json([
            'pesan'   => 'Data uji lama berhasil dihapus.',
            'dihapus' => ['presensi' => $hapusPresensi, 'token' => $hapusToken, 'pengguna' => $hapusPengguna],
        ]);
    }

    // ── Helpers ───────────────────────────────────────────────
    private function haversine(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R    = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);
        $a    = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        return $R * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }

    private function statistik(array $v): array
    {
        if (empty($v)) {
            return [];
        }
        sort($v);
        $n      = count($v);
        $mean   = array_sum($v) / $n;
        $var    = array_reduce($v, fn($acc, $x) => $acc + ($x - $mean) ** 2, 0) / max($n - 1, 1);
        $stdev  = sqrt($var);
        $median = $n % 2 === 0 ? ($v[$n / 2 - 1] + $v[$n / 2]) / 2 : $v[intdiv($n, 2)];
        $pct    = fn($p) => $v[min((int) floor($n * $p), $n - 1)];

        return [
            'n'         => $n,
            'min_ms'    => $v[0],
            'max_ms'    => $v[$n - 1],
            'mean_ms'   => round($mean, 2),
            'median_ms' => $median,
            'stdev_ms'  => round($stdev, 2),
            'p90_ms'    => $pct(0.90),
            'p95_ms'    => $pct(0.95),
            'p99_ms'    => $pct(0.99),
        ];
    }
}
