<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class MockDetectionLogController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'presensi_id' => ['nullable', 'uuid'],
            'mock_location' => ['required', 'boolean'],
            'is_real_device' => ['required', 'boolean'],
            'is_rooted' => ['required', 'boolean'],
            'is_dev_mode' => ['required', 'boolean'],
            'final_status' => ['required', 'in:HARD_BLOCK_MOCK,HARD_BLOCK_EMULATOR,HARD_BLOCK_SYNERGISTIC_THREAT,HARD_BLOCK_SENSOR_FAILED,SOFT_RISK,SAFE'],
            'message' => ['nullable', 'string'],
        ]);

        $user = $request->user();
        $karyawanId = $this->ensureKaryawanProfile($user->id);

        // Store raw detection signals and final status using the new non-scoring contract.
        $logId = (string) Str::uuid();

        DB::table('log_deteksi')->insert([
            'id' => $logId,
            'presensi_id' => $validated['presensi_id'] ?? null,
            'karyawan_id' => $karyawanId,
            'mock_location' => $validated['mock_location'],
            'is_real_device' => $validated['is_real_device'],
            'is_rooted' => $validated['is_rooted'],
            'is_dev_mode' => $validated['is_dev_mode'],
            'final_status' => $validated['final_status'],
            'message' => $validated['message'] ?? null,
            'detected_at' => now(),
            'created_at' => now(),
        ]);

        return ApiResponse::success('Log deteksi berhasil disimpan.', [
            'id' => $logId,
        ], 201);
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
