<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $today = Carbon::now()->toDateString();

        $totalKaryawanAktif = DB::table('karyawan as k')
            ->join('pengguna as p', 'p.id', '=', 'k.pengguna_id')
            ->where('p.is_active', true)
            ->count();

        $totalPresensiHariIni = DB::table('presensi')
            ->whereDate('tanggal', $today)
            ->count();

        $totalHadir = DB::table('presensi')
            ->whereDate('tanggal', $today)
            ->where('status', 'hadir')
            ->count();

        $totalTerlambat = DB::table('presensi')
            ->whereDate('tanggal', $today)
            ->where('status', 'terlambat')
            ->count();

        $totalTidakHadir = max($totalKaryawanAktif - $totalPresensiHariIni, 0);

        $weeklyRaw = DB::table('presensi')
            ->selectRaw('tanggal, count(*) as total')
            ->whereDate('tanggal', '>=', Carbon::now()->subDays(6)->toDateString())
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get()
            ->keyBy('tanggal');

        $weeklyLabels = [];
        $weeklyValues = [];

        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->toDateString();
            $weeklyLabels[] = Carbon::parse($date)->format('d M');
            $weeklyValues[] = (int) ($weeklyRaw[$date]->total ?? 0);
        }

        return view('dashboard.index', [
            'summary' => [
                'total_karyawan_aktif' => $totalKaryawanAktif,
                'total_presensi_hari_ini' => $totalPresensiHariIni,
                'total_hadir' => $totalHadir,
                'total_terlambat' => $totalTerlambat,
                'total_tidak_hadir' => $totalTidakHadir,
            ],
            'weeklyLabels' => $weeklyLabels,
            'weeklyValues' => $weeklyValues,
        ]);
    }
}
