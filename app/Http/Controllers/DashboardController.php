<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        return view('dashboard.index');
    }

    public function getChartData()
    {
        // 1. Tentukan tahun ini dan tahun lalu secara dinamis
        $currentYear = Carbon::now()->year; // Hasil: 2025
        $previousYear = $currentYear - 1;  // Hasil: 2024

        // 2. Modifikasi query untuk HANYA mengambil data dari dua tahun tersebut
        $kegiatans = DB::table('kegiatans')
            ->select(
                DB::raw('YEAR(tanggal_mulai) as year'),
                DB::raw('MONTH(tanggal_mulai) as month'),
                DB::raw('COUNT(*) as total')
            )
            // Tambahkan klausa 'whereIn' untuk memfilter tahun
            ->whereIn(DB::raw('YEAR(tanggal_mulai)'), [$currentYear, $previousYear])
            ->groupBy('year', 'month')
            ->orderBy('year', 'asc')
            ->orderBy('month', 'asc')
            ->get();

        // 3. Proses data mentah menjadi format untuk ApexCharts (logika ini tetap sama)
        $formattedData = [];
        foreach ($kegiatans as $kegiatan) {
            $year = $kegiatan->year;
            $month = $kegiatan->month;
            $total = $kegiatan->total;

            if (!isset($formattedData[$year])) {
                $formattedData[$year] = [
                    'name' => (string)$year, // Label tahun akan dinamis (misal: "2025" dan "2024")
                    'data' => array_fill(0, 12, 0)
                ];
            }

            $formattedData[$year]['data'][$month - 1] = $total;
        }

        // 4. Siapkan data final untuk dikirim sebagai JSON
        $finalChartData = [
            'categories' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            'series' => array_values($formattedData)
        ];

        return response()->json($finalChartData);
    }
}
