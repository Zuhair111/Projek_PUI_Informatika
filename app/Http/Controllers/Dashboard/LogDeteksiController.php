<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LogDeteksiController extends Controller
{
    public function index(Request $request): View
    {
        $rows = DB::table('log_deteksi as ld')
            ->join('karyawan as k', 'k.id', '=', 'ld.karyawan_id')
            ->join('pengguna as p', 'p.id', '=', 'k.pengguna_id')
            ->select([
                'ld.id',
                'p.nama as nama_karyawan',
                'ld.detected_at',
                'ld.mock_location',
                'ld.is_real_device',
                'ld.is_rooted',
                'ld.is_dev_mode',
                'ld.final_status',
                'ld.message',
            ])
            ->orderByDesc('ld.detected_at')
            ->paginate(20);

        return view('dashboard.log-deteksi.index', [
            'rows' => $rows,
        ]);
    }
}
