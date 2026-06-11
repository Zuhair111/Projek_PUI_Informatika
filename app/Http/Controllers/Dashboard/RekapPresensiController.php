<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class RekapPresensiController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'tanggal' => (string) $request->query('tanggal', ''),
            'departemen' => (string) $request->query('departemen', ''),
            'nama' => (string) $request->query('nama', ''),
        ];

        $query = $this->buildQuery($filters);

        $rows = $query->paginate(15)->withQueryString();

        return view('dashboard.rekap.index', [
            'rows' => $rows,
            'filters' => $filters,
        ]);
    }

    public function exportPdf(Request $request): Response
    {
        $rows = $this->buildQuery([
            'tanggal' => (string) $request->query('tanggal', ''),
            'departemen' => (string) $request->query('departemen', ''),
            'nama' => (string) $request->query('nama', ''),
        ])->get();

        $pdf = Pdf::loadView('dashboard.rekap.pdf', ['rows' => $rows]);

        return $pdf->download('rekap-presensi.pdf');
    }

    public function exportExcel(Request $request): BinaryFileResponse
    {
        $rows = $this->buildQuery([
            'tanggal' => (string) $request->query('tanggal', ''),
            'departemen' => (string) $request->query('departemen', ''),
            'nama' => (string) $request->query('nama', ''),
        ])->get();

        $exportRows = $rows->map(static function ($row): array {
            return [
                'Nama Karyawan' => $row->nama_karyawan,
                'Departemen' => $row->departemen,
                'Tanggal' => $row->tanggal,
                'Waktu Masuk' => $row->check_in_at,
                'Waktu Pulang' => $row->check_out_at,
                'Status Presensi' => $row->status,
                'Hasil Deteksi Mock Location' => $row->hasil_deteksi,
            ];
        })->toArray();

        return Excel::download(new class($exportRows) implements \Maatwebsite\Excel\Concerns\FromArray {
            public function __construct(private readonly array $rows)
            {
            }

            public function array(): array
            {
                return $this->rows;
            }
        }, 'rekap-presensi.xlsx');
    }

    private function buildQuery(array $filters)
    {
        $query = DB::table('presensi as pr')
            ->join('karyawan as k', 'k.id', '=', 'pr.karyawan_id')
            ->join('pengguna as p', 'p.id', '=', 'k.pengguna_id')
            ->leftJoin('log_deteksi as ld', 'ld.presensi_id', '=', 'pr.id')
            ->select([
                'pr.id',
                'p.nama as nama_karyawan',
                'k.departemen',
                'pr.tanggal',
                'pr.check_in_at',
                'pr.check_out_at',
                'pr.status',
                DB::raw("COALESCE(ld.jenis_deteksi, 'aman') as hasil_deteksi"),
            ])
            ->orderByDesc('pr.tanggal')
            ->orderByDesc('pr.created_at');

        if ($filters['tanggal'] !== '') {
            $query->whereDate('pr.tanggal', $filters['tanggal']);
        }

        if ($filters['departemen'] !== '') {
            $query->where('k.departemen', 'ilike', "%{$filters['departemen']}%");
        }

        if ($filters['nama'] !== '') {
            $query->where('p.nama', 'ilike', "%{$filters['nama']}%");
        }

        return $query;
    }
}
