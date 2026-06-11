<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class GeofencingController extends Controller
{
    public function active(): JsonResponse
    {
        return $this->index();
    }

    public function index(): JsonResponse
    {
        // Return only active geofencing points for mobile map and attendance checks.
        $data = DB::table('geofencing')
            ->select(['id', 'nama_lokasi', 'latitude', 'longitude', 'radius_meter'])
            ->where('aktif', true)
            ->orderBy('nama_lokasi')
            ->get();

        return ApiResponse::success('Data geofencing aktif berhasil diambil.', $data->toArray());
    }
}
