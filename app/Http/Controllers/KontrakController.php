<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\Kegiatan;
use App\Services\HonorCalculator;
use Illuminate\Http\Request;
use App\Models\PetugasKegiatan;
use Illuminate\Support\Facades\DB;

class KontrakController extends Controller
{
    public function __construct(
        private readonly HonorCalculator $honor,
    ) {
    }

    public function index(Request $request)
    {
        $bulan_sekarang = $request->input('bulan', date('n'));
        $tahun_sekarang = $request->input('tahun', date('Y'));

        if ($bulan_sekarang < 1 || $bulan_sekarang > 12) {
            abort(400, 'Bulan tidak valid.');
        }

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

        $tahun_range = range(date('Y') - 1, date('Y'));
        $slug = strtolower($nama_bulan[$bulan_sekarang]) . '-' . $tahun_sekarang;

        $data_petugas = PetugasKegiatan::query()
            ->select(
                'petugas_kegiatans.nik',
                'mitras.nama_mitra',
                'mitras.posisi',
                'wilayah_tugas.kode_wilayah',
                'wilayah_tugas.honor_pendataan',
                'wilayah_tugas.honor_pengolahan',
                DB::raw('SUM(petugas_kegiatans.honor) as total_honor')
            )
            ->join('kegiatans', 'petugas_kegiatans.kegiatan_id', '=', 'kegiatans.id')
            ->join('mitras', 'petugas_kegiatans.nik', '=', 'mitras.nik')
            ->join('wilayah_tugas', 'mitras.wilayah_id', '=', 'wilayah_tugas.id')
            ->whereNull('kegiatans.deleted_at')
            ->whereMonth('kegiatans.tanggal_mulai', $bulan_sekarang)
            ->whereYear('kegiatans.tanggal_mulai', $tahun_sekarang)
            ->whereNull('petugas_kegiatans.deleted_at')
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

        $kegiatan_petugas_bulan = Kegiatan::query()
            ->with('timKerja')
            ->select('petugas_kegiatans.*', 'kegiatans.*')
            ->join('petugas_kegiatans', function ($join) {
                $join->on('kegiatans.id', '=', 'petugas_kegiatans.kegiatan_id')
                    ->whereNull('petugas_kegiatans.deleted_at');
            })
            ->whereIn('petugas_kegiatans.nik', $nik_petugas)
            ->whereMonth('kegiatans.tanggal_mulai', $bulan_sekarang)
            ->whereYear('kegiatans.tanggal_mulai', $tahun_sekarang)
            ->get()
            ->groupBy('nik');

        $rekap_petugas_bulan = $data_petugas->map(function ($item) use ($kegiatan_petugas_bulan) {
            $kegiatans = $kegiatan_petugas_bulan->get($item->nik, collect());
            $has_pengolahan = $kegiatans->contains('tim_kerja_id', 1);
            $honor_max = $this->honor->cap(
                $item->posisi,
                (int) $item->honor_pendataan,
                (int) $item->honor_pengolahan,
                $has_pengolahan,
            );

            $rekap_kegiatan = $kegiatans->map(function ($detail_kegiatan) {
                $now = now();
                $tanggal_mulai = Carbon::parse($detail_kegiatan->tanggal_mulai);
                $tanggal_selesai = Carbon::parse($detail_kegiatan->tanggal_selesai);

                if ($now->lessThan($tanggal_mulai)) {
                    $status = 'bx bx-calendar-exclamation'; //'pending';
                } elseif ($now->between($tanggal_mulai, $tanggal_selesai)) {
                    $status = 'bx bx-time-five'; //'ongoing';
                } else {
                    $status = 'bx bx-task'; //'completed';
                }

                $nama_kegiatan = $detail_kegiatan->is_ob ? '[O-B] ' . $detail_kegiatan->nama_kegiatan : $detail_kegiatan->nama_kegiatan;

                return [
                    'nama_kegiatan'      => $nama_kegiatan,
                    'slug'               => $detail_kegiatan->slug,
                    'tanggal_mulai'      => $detail_kegiatan->tanggal_mulai,
                    'tanggal_selesai'    => $detail_kegiatan->tanggal_selesai,
                    'beban_anggaran'     => $detail_kegiatan->beban_anggaran,
                    'alias_tim_kerja'    => $detail_kegiatan->timKerja->alias_tim_kerja,
                    'bertugas_sebagai'   => $detail_kegiatan->bertugas_sebagai,
                    'beban_kerja'        => $detail_kegiatan->beban_kerja,
                    'satuan_beban_kerja' => $detail_kegiatan->satuan_beban_kerja,
                    'honor'              => $detail_kegiatan->honor,
                    'status'             => $status,
                ];
            })->values();

            return [
                'nik'           => $item->nik,
                'nama_mitra'    => $item->nama_mitra,
                'total_honor'   => $item->total_honor,
                'wilayah_tugas' => $item->kode_wilayah,
                'honor_max'     => $honor_max,
                'sisa_honor'    => max($honor_max - $item->total_honor, 0),
                'melebihi'      => $item->total_honor > $honor_max,
                'kegiatan'      => $rekap_kegiatan,
            ];
        })->sortByDesc('total_honor')->values();

        return view('kontrak.index', [
            'nama_bulan'     => $nama_bulan,
            'bulan_sekarang' => (int) $bulan_sekarang,
            'tahun_sekarang' => (int) $tahun_sekarang,
            'tahun_range'    => $tahun_range,
            'slug'           => $slug,
            'petugas_bulan'  => $rekap_petugas_bulan,
        ]);
    }
}
