<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Pengguna;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AuthController extends Controller
{
    public function showLoginForm(): View
    {
        return view('dashboard.auth.login');
    }

    public function showRegisterForm(): View
    {
        return view('dashboard.auth.register');
    }

    public function login(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string', 'min:6'],
        ]);

        $pengguna = Pengguna::query()
            ->where('email', $validated['email'])
            ->where('role', 'administrator')
            ->first();

        if (!$pengguna || !Hash::check($validated['password'], $pengguna->password_hash)) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Email atau password salah.']);
        }

        if (!$pengguna->is_active) {
            return back()
                ->withInput($request->only('email'))
                ->withErrors(['email' => 'Akun tidak aktif.']);
        }

        Auth::login($pengguna, true);
        $request->session()->regenerate();

        return redirect()->route('dashboard.index');
    }

    public function logout(Request $request): RedirectResponse
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('dashboard.login.form');
    }

    public function register(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', 'unique:pengguna,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        DB::transaction(function () use ($validated): void {
            $penggunaId = (string) Str::uuid();

            DB::table('pengguna')->insert([
                'id' => $penggunaId,
                'nama' => $validated['nama'],
                'email' => $validated['email'],
                'password_hash' => Hash::make($validated['password']),
                'role' => 'administrator',
                'is_active' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('administrator')->insert([
                'id' => (string) Str::uuid(),
                'pengguna_id' => $penggunaId,
                'level_akses' => 'admin',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()
            ->route('dashboard.login.form')
            ->with('success', 'Registrasi administrator berhasil. Silakan login.');
    }
}
