<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\Pengguna;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', 'unique:pengguna,email'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);

        $pengguna = Pengguna::query()->create([
            'id' => (string) Str::uuid(),
            'nama' => $validated['nama'],
            'email' => $validated['email'],
            'password_hash' => Hash::make($validated['password']),
            'role' => 'karyawan',
            'is_active' => true,
        ]);

        $karyawanId = $this->ensureKaryawanProfile($pengguna->id);

        $token = $pengguna->createToken('mobile-or-web-token')->plainTextToken;

        return ApiResponse::success('Registrasi berhasil.', [
            'jwt_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $pengguna->id,
                'nama' => $pengguna->nama,
                'email' => $pengguna->email,
                'role' => $pengguna->role,
                'karyawan_id' => $karyawanId,
            ],
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $pengguna = Pengguna::query()
            ->where('email', $validated['email'])
            ->first();

        if (!$pengguna || !Hash::check($validated['password'], $pengguna->password_hash)) {
            return ApiResponse::error('Email atau password salah.', [
                'credentials' => ['Email atau password tidak cocok.'],
            ], 401);
        }

        if (!$pengguna->is_active) {
            return ApiResponse::error('Akun tidak aktif.', [
                'account' => ['Silakan hubungi administrator.'],
            ], 403);
        }

        $karyawanId = null;
        if ($pengguna->role === 'karyawan') {
            $karyawanId = $this->ensureKaryawanProfile($pengguna->id);
        }

        $token = $pengguna->createToken('mobile-or-web-token')->plainTextToken;

        return ApiResponse::success('Login berhasil.', [
            'jwt_token' => $token,
            'token_type' => 'Bearer',
            'user' => [
                'id' => $pengguna->id,
                'nama' => $pengguna->nama,
                'email' => $pengguna->email,
                'role' => $pengguna->role,
                'karyawan_id' => $karyawanId,
            ],
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $user = $request->user();

        if ($user && $user->currentAccessToken()) {
            $user->currentAccessToken()->delete();
        }

        return ApiResponse::success('Logout berhasil.');
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
