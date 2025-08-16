<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class KontrakController extends Controller
{
    public function index(Request $request)
    {
        $nama_bulan = [
            1 => 'Januari',
            'Februari',
            'Maret',
            'April',
            'Mei',
            'Juni',
            'Juli',
            'Agustus',
            'September',
            'Oktober',
            'November',
            'Desember'
        ];

        $bulan_sekarang = $request->input('bulan', date('n'));
        $tahun_sekarang = $request->input('tahun', date('Y'));

        if (!array_key_exists($bulan_sekarang, $nama_bulan)) {
            abort(400, 'Bulan tidak valid.');
        }

        $tahun_mulai = date('Y') - 1;
        $tahun_range = range($tahun_mulai, date('Y'));
        $slug = strtolower($nama_bulan[$bulan_sekarang]) . '-' . $tahun_sekarang;

        $data_petugas = DB::table('petugas_kegiatans')
            ->join('kegiatans', 'petugas_kegiatans.kegiatan_id', '=', 'kegiatans.id')
            ->join('mitras', 'petugas_kegiatans.nik', '=', 'mitras.nik')
            ->join('wilayah_tugas', 'petugas_kegiatans.wilayah_tugas', '=', 'wilayah_tugas.kode_wilayah')
            ->whereMonth('kegiatans.tanggal_mulai', $bulan_sekarang)
            ->whereYear('kegiatans.tanggal_mulai', $tahun_sekarang)
            ->select(
                'petugas_kegiatans.nik',
                'mitras.nama_mitra',
                'mitras.posisi',
                'wilayah_tugas.kode_wilayah',
                'wilayah_tugas.honor_pendataan',
                'wilayah_tugas.honor_pengolahan',
                DB::raw('SUM(petugas_kegiatans.honor) as total_honor')
            )
            ->groupBy(
                'petugas_kegiatans.nik',
                'mitras.nama_mitra',
                'mitras.posisi',
                'wilayah_tugas.kode_wilayah',
                'wilayah_tugas.honor_pendataan',
                'wilayah_tugas.honor_pengolahan'
            )
            ->get();

        $nik_petugas = $data_petugas->pluck('nik');
        $now = now();

        $kegiatan_petugas_bulan = DB::table('petugas_kegiatans')
            ->join('kegiatans', 'petugas_kegiatans.kegiatan_id', '=', 'kegiatans.id')
            ->whereIn('petugas_kegiatans.nik', $nik_petugas)
            ->whereMonth('kegiatans.tanggal_mulai', $bulan_sekarang)
            ->whereYear('kegiatans.tanggal_mulai', $tahun_sekarang)
            ->select(
                'petugas_kegiatans.nik',
                'kegiatans.nama_kegiatan',
                'kegiatans.tanggal_mulai',
                'kegiatans.tanggal_selesai',
                'kegiatans.tim_kerja_id'
            )
            ->distinct()
            ->get()
            ->groupBy('nik');

        $rekap_petugas_bulan = $data_petugas->map(function ($item) use ($kegiatan_petugas_bulan, $now) {
            $rekap_kegiatan_bulan = ($kegiatan_petugas_bulan[$item->nik] ?? collect())->map(function ($kegiatan) use ($now) {
                if ($now->lt($kegiatan->tanggal_mulai)) {
                    $status = 'Belum Mulai';
                } elseif ($now->between($kegiatan->tanggal_mulai, $kegiatan->tanggal_selesai)) {
                    $status = 'Sedang Berjalan';
                } else {
                    $status = 'Selesai';
                }

                return [
                    'nama_kegiatan'   => $kegiatan->nama_kegiatan,
                    'tanggal_mulai'   => $kegiatan->tanggal_mulai,
                    'tanggal_selesai' => $kegiatan->tanggal_selesai,
                    'tim_kerja_id'    => $kegiatan->tim_kerja_id,
                    'status'          => $status,
                ];
            });

            if ($item->posisi === 'Mitra Pendataan') {
                $honor_max = $item->honor_pendataan;
            } elseif ($item->posisi === 'Mitra Pengolahan') {
                $honor_max = $item->honor_pengolahan;
            } elseif ($item->posisi === 'Mitra (Pendataan dan Pengolahan)') {
                $honor_max = $rekap_kegiatan_bulan->contains('tim_kerja_id', 1)
                    ? $item->honor_pengolahan
                    : $item->honor_pendataan;
            } else {
                $honor_max = 0;
            }

            return [
                'nik'           => $item->nik,
                'nama_mitra'    => $item->nama_mitra,
                'total_honor'   => $item->total_honor,
                'wilayah_tugas' => $item->kode_wilayah,
                'honor_max'     => $honor_max,
                'sisa_honor'    => max($honor_max - $item->total_honor, 0),
                'melebihi'      => $item->total_honor > $honor_max,
                'kegiatan'      => $rekap_kegiatan_bulan,
            ];
        })->sortByDesc('total_honor')->values();

        return view('kontrak.index', [
            'nama_bulan'     => $nama_bulan,
            'bulan_sekarang' => $bulan_sekarang,
            'tahun_sekarang' => $tahun_sekarang,
            'tahun_range'    => $tahun_range,
            'slug'           => $slug,
            'petugas_bulan'  => $rekap_petugas_bulan
        ]);
    }
}
