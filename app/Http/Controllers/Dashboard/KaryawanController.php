<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;

class KaryawanController extends Controller
{
    public function index(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $query = DB::table('karyawan as k')
            ->join('pengguna as p', 'p.id', '=', 'k.pengguna_id')
            ->select([
                'k.id',
                'k.nip',
                'k.departemen',
                'k.jabatan',
                'p.nama',
                'p.email',
                'p.is_active',
            ])
            ->orderBy('p.nama');

        if ($search !== '') {
            $query->where(function ($q) use ($search): void {
                $q->where('p.nama', 'ilike', "%{$search}%")
                    ->orWhere('k.nip', 'ilike', "%{$search}%")
                    ->orWhere('k.departemen', 'ilike', "%{$search}%")
                    ->orWhere('k.jabatan', 'ilike', "%{$search}%");
            });
        }

        $karyawan = $query->paginate(10)->withQueryString();

        return view('dashboard.karyawan.index', [
            'karyawan' => $karyawan,
            'search' => $search,
        ]);
    }

    public function create(): View
    {
        return view('dashboard.karyawan.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', 'unique:pengguna,email'],
            'password' => ['required', 'string', 'min:6'],
            'nip' => ['required', 'string', 'max:30', 'unique:karyawan,nip'],
            'departemen' => ['required', 'string', 'max:100'],
            'jabatan' => ['required', 'string', 'max:100'],
            'no_hp' => ['nullable', 'string', 'max:25'],
            'is_active' => ['required', 'boolean'],
        ]);

        DB::transaction(function () use ($validated): void {
            $penggunaId = (string) Str::uuid();
            $karyawanId = (string) Str::uuid();

            DB::table('pengguna')->insert([
                'id' => $penggunaId,
                'nama' => $validated['nama'],
                'email' => $validated['email'],
                'password_hash' => Hash::make($validated['password']),
                'role' => 'karyawan',
                'is_active' => $validated['is_active'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::table('karyawan')->insert([
                'id' => $karyawanId,
                'pengguna_id' => $penggunaId,
                'nip' => $validated['nip'],
                'departemen' => $validated['departemen'],
                'jabatan' => $validated['jabatan'],
                'no_hp' => $validated['no_hp'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        });

        return redirect()->route('dashboard.karyawan.index')->with('success', 'Data karyawan berhasil ditambahkan.');
    }

    public function edit(string $karyawan): View
    {
        $item = DB::table('karyawan as k')
            ->join('pengguna as p', 'p.id', '=', 'k.pengguna_id')
            ->where('k.id', $karyawan)
            ->select([
                'k.id',
                'k.pengguna_id',
                'k.nip',
                'k.departemen',
                'k.jabatan',
                'k.no_hp',
                'p.nama',
                'p.email',
                'p.is_active',
            ])
            ->firstOrFail();

        return view('dashboard.karyawan.edit', ['item' => $item]);
    }

    public function update(Request $request, string $karyawan): RedirectResponse
    {
        $item = DB::table('karyawan')->where('id', $karyawan)->firstOrFail();

        $validated = $request->validate([
            'nama' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:150', "unique:pengguna,email,{$item->pengguna_id},id"],
            'password' => ['nullable', 'string', 'min:6'],
            'nip' => ['required', 'string', 'max:30', "unique:karyawan,nip,{$karyawan},id"],
            'departemen' => ['required', 'string', 'max:100'],
            'jabatan' => ['required', 'string', 'max:100'],
            'no_hp' => ['nullable', 'string', 'max:25'],
            'is_active' => ['required', 'boolean'],
        ]);

        DB::transaction(function () use ($validated, $item, $karyawan): void {
            $penggunaPayload = [
                'nama' => $validated['nama'],
                'email' => $validated['email'],
                'is_active' => $validated['is_active'],
                'updated_at' => now(),
            ];

            if (!empty($validated['password'])) {
                $penggunaPayload['password_hash'] = Hash::make($validated['password']);
            }

            DB::table('pengguna')->where('id', $item->pengguna_id)->update($penggunaPayload);

            DB::table('karyawan')->where('id', $karyawan)->update([
                'nip' => $validated['nip'],
                'departemen' => $validated['departemen'],
                'jabatan' => $validated['jabatan'],
                'no_hp' => $validated['no_hp'] ?? null,
                'updated_at' => now(),
            ]);
        });

        return redirect()->route('dashboard.karyawan.index')->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(string $karyawan): RedirectResponse
    {
        $item = DB::table('karyawan')->where('id', $karyawan)->firstOrFail();

        DB::transaction(function () use ($item, $karyawan): void {
            DB::table('karyawan')->where('id', $karyawan)->delete();
            DB::table('pengguna')->where('id', $item->pengguna_id)->delete();
        });

        return redirect()->route('dashboard.karyawan.index')->with('success', 'Data karyawan berhasil dihapus.');
    }
}
