<?php

namespace App\Http\Controllers;

use ZipArchive;
use Carbon\Carbon;
use App\Models\Kegiatan;
use Illuminate\Http\Request;
use App\Helpers\NumberToWords;
use App\Models\PetugasKegiatan;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\TemplateProcessor;

class DownloadController extends Controller
{
    /**
     * ✨ OPTIMASI 3: Helper method untuk mengisolasi logika tanggal.
     * @param \App\Models\Kegiatan $kegiatan
     * @return array
     */
    private function prepareDateVariables(Kegiatan $kegiatan): array
    {
        // ambil tanggal kegiatan sebagai dasar penentuan tanggal kontrak
        // tanggal kontrak adalah tanggal 1 di bulan kegiatan dimulai
        // jika tanggal jatuh pada hari sabtu/minggu, maka tanggal kontrak adalah hari jumat di bulan sebelumnya
        $base_tanggal_kegiatan = Carbon::parse($kegiatan->tanggal_mulai);
        $base_tanggal = $base_tanggal_kegiatan->copy()->startOfMonth();
        $tanggal_kontrak_full = ($base_tanggal->isSaturday() || $base_tanggal->isSunday())
            ? $base_tanggal->previousWeekday()
            : $base_tanggal;

        $tanggal_kegiatan_selesai = Carbon::parse($kegiatan->tanggal_selesai);

        return [
            'tanggal_kontrak'         => $tanggal_kontrak_full->format('d'),
            'bulan_kontrak'           => NumberToWords::monthName($tanggal_kontrak_full->format('m')),
            'tahun_kontrak'           => $tanggal_kontrak_full->format('Y'),
            'tanggal_kegiatan'        => $base_tanggal_kegiatan->format('d'),
            'bulan_kegiatan'          => NumberToWords::monthName($base_tanggal_kegiatan->format('m')),
            'tahun_kegiatan'          => $base_tanggal_kegiatan->format('Y'),
            'hari_bast'               => NumberToWords::dayName($tanggal_kegiatan_selesai->format('l')),
            'tanggal_bast'            => $tanggal_kegiatan_selesai->format('d'),
            'tanggal_bast_terbilang'  => ucfirst(NumberToWords::toWords((int) $tanggal_kegiatan_selesai->format('d'))),
            'bulan_bast'              => NumberToWords::monthName($tanggal_kegiatan_selesai->format('m')),
            'tahun_bast'              => $tanggal_kegiatan_selesai->format('Y'),
            'tahun_bast_terbilang'    => ucfirst(NumberToWords::toWords((int) $tanggal_kegiatan_selesai->format('Y'))),
        ];
    }

    public function downloadBAST(Kegiatan $kegiatan)
    {
        if (!$kegiatan) {
            return to_route('kegiatan.index')
                ->with('error', 'Kegiatan tidak ditemukan.');
        }

        if (!$kegiatan->is_generated) {
            return to_route('kegiatan.index')
                ->with('error', 'BAST belum bisa diunduh karena nomor belum digenerate.');
        }

        $petugas_kegiatan = PetugasKegiatan::join('nomor_kontraks', function ($join) {
            $join->on('petugas_kegiatans.nik', '=', 'nomor_kontraks.nik')
                ->on('petugas_kegiatans.kegiatan_id', '=', 'nomor_kontraks.kegiatan_id');
        })
            ->join('mitras', 'petugas_kegiatans.nik', '=', 'mitras.nik')
            ->join('kegiatans', 'petugas_kegiatans.kegiatan_id', '=', 'kegiatans.id')
            ->join('tim_kerjas', 'kegiatans.tim_kerja_id', '=', 'tim_kerjas.id')
            ->where('petugas_kegiatans.kegiatan_id', $kegiatan->id)
            ->select(
                'petugas_kegiatans.*',
                'nomor_kontraks.nomor_kontrak',
                'nomor_kontraks.nomor_bast',
                'mitras.nama_mitra',
                'mitras.alamat',
                'tim_kerjas.alias_tim_kerja'
            )
            ->get();

        if ($petugas_kegiatan->isEmpty()) {
            return to_route('kegiatan.index')
                ->with('error', 'Belum ada petugas di kegiatan ini!');
        }

        $template_path = storage_path('app/public/template/template_bast.docx');

        if (!file_exists($template_path)) {
            return to_route('kegiatan.index')
                ->with('error', 'Template BAST tidak ditemukan di server.');
        }

        $zip = new ZipArchive();

        $zip_file_name = storage_path('app/public/bast/BAST_' . str_replace(' ', '_', $kegiatan->nama_kegiatan) . '.zip');

        if ($zip->open($zip_file_name, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return to_route('kegiatan.index')
                ->with('error', 'Gagal membuat file zip.');
        }

        $dateVars = $this->prepareDateVariables($kegiatan);

        $bast_files = [];

        foreach ($petugas_kegiatan as $p) {
            $templateProcessor = new TemplateProcessor($template_path);

            $full_nomor_bast = str_pad($p->nomor_bast, 3, '0', STR_PAD_LEFT) . "/1201_BAST/" . $dateVars['tahun_bast'];
            $full_nomor_kontrak = str_pad($p->nomor_kontrak, 3, '0', STR_PAD_LEFT) . "/1201_MITRA/" . $dateVars['tahun_kontrak'];

            $data = [
                'bulan_kegiatan_kapital' => strtoupper($dateVars['bulan_kegiatan']),
                'tahun_kegiatan'         => $dateVars['tahun_kegiatan'],
                'nomor_bast'             => $full_nomor_bast,
                'hari'                   => $dateVars['hari_bast'],
                'tanggal_terbilang'      => $dateVars['tanggal_bast_terbilang'],
                'bulan'                  => $dateVars['bulan_bast'],
                'tahun_terbilang'        => $dateVars['tahun_bast_terbilang'],
                'nama_mitra'             => ucwords(strtolower($p->nama_mitra)),
                'alamat'                 => $p->alamat,
                'bulan_kegiatan'         => $dateVars['bulan_kegiatan'],
                'tanggal_kegiatan'       => $dateVars['tanggal_kegiatan'],
                'nomor_kontrak'          => $full_nomor_kontrak,
                'tanggal_kontrak'        => $dateVars['tanggal_kontrak'],
                'bulan_kontrak'          => $dateVars['bulan_kontrak'],
                'tahun_kontrak'          => $dateVars['tahun_kontrak'],
                'nama_kegiatan'          => $kegiatan->nama_kegiatan,
                'beban'                  => $p->beban_kerja,
                'satuan'                 => $p->satuan_beban_kerja,
                'tim_kerja'              => $p->alias_tim_kerja,
            ];

            $templateProcessor->setValues($data);

            $output_path = storage_path('app/public/bast/BAST_' . str_replace(' ', '_', $data['nama_mitra']) . '.docx');

            $templateProcessor->saveAs($output_path);

            $zip->addFile($output_path, 'BAST_' . str_replace(' ', '_', $data['nama_mitra']) . '.docx');

            $bast_files[] = $output_path;
        }

        $zip->close();

        foreach ($bast_files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
        return response()->download($zip_file_name)->deleteFileAfterSend(true);
    }

    public function downloadSPK(Request $request, $slug)
    {
        // ambil daftar nik petugas yang dipilih dari query parameter ids
        $selected_petugas = $request->query('ids') ? explode(',', $request->query('ids')) : [];

        if (empty($selected_petugas)) {
            return to_route('kegiatan.index')
                ->with('error', 'Tidak ada petugas yang dipilih untuk diunduh SPK-nya.');
        }

        $bulan_tahun = str_replace('-', ' ', $slug);

        $date = Carbon::parseFromLocale($bulan_tahun);

        $list_petugas_bulan = PetugasKegiatan::join('kegiatans', 'petugas_kegiatans.kegiatan_id', '=', 'kegiatans.id')
            ->join('nomor_kontraks', 'petugas_kegiatans.nik', '=', 'nomor_kontraks.nik')
            ->join('mitras', 'petugas_kegiatans.nik', '=', 'mitras.nik')
            ->whereMonth('kegiatans.tanggal_mulai', $date->format('m'))
            ->whereYear('kegiatans.tanggal_mulai', $date->format('Y'))
            ->whereIn('petugas_kegiatans.nik', $selected_petugas)
            ->select('petugas_kegiatans.*', 'kegiatans.nama_kegiatan', 'kegiatans.tim_kerja_id', 'kegiatans.is_ob', 'kegiatans.tanggal_mulai', 'kegiatans.tanggal_selesai', 'kegiatans.beban_anggaran', 'nomor_kontraks.nomor_kontrak', 'mitras.*')
            ->get();

        $rekap_kegiatan_petugas_bulan = [];
        foreach ($list_petugas_bulan as $petugas) {
            if ($petugas->is_ob) {
                continue; // skip jika petugas adalah OB
            }

            if (!isset($rekap_kegiatan_petugas_bulan[$petugas->nik])) {
                $rekap_kegiatan_petugas_bulan[$petugas->nik] = [
                    'petugas'          => $petugas->nama_mitra,
                    'kegiatan'         => [],
                ];
            }
            $rekap_kegiatan_petugas_bulan[$petugas->nik]['kegiatan'][] = $petugas;
        }
        dd($rekap_kegiatan_petugas_bulan); // IGNORE

        $template_path = storage_path('app/public/template/template_kontrak.docx');

        if (!file_exists($template_path)) {
            return to_route('kegiatan.index')
                ->with('error', 'Template BAST tidak ditemukan di server.');
        }

        $zip = new ZipArchive();

        $zip_file_name = storage_path('app/public/bast/KONTRAK_' . str_replace(' ', '_', strtoupper($slug)) . '.zip');

        if ($zip->open($zip_file_name, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return to_route('kegiatan.index')
                ->with('error', 'Gagal membuat file zip.');
        }

        $kontrak_files = [];

        foreach ($rekap_kegiatan_petugas_bulan as $r) {
            $templateProcessor = new TemplateProcessor($template_path);

            $base_tanggal_kegiatan = Carbon::parse($r['kegiatan'][0]->tanggal_mulai);
            $base_tanggals = $base_tanggal_kegiatan->copy()->startOfMonth();
            $base_tanggal = ($base_tanggals->isStartOfYear()) ? $base_tanggals->nextWeekday() : $base_tanggals;
            $tanggal_kontrak_full = ($base_tanggal->isSaturday() || $base_tanggal->isSunday())
                ? $base_tanggal->previousWeekday()
                : $base_tanggal;

            $tanggal_kontrak    = $tanggal_kontrak_full->format('d');
            $hari_kontrak       = NumberToWords::dayName($tanggal_kontrak_full->format('l'));
            $bulan_kontrak      = NumberToWords::monthName($tanggal_kontrak_full->format('m'));
            $tahun_kontrak      = $tanggal_kontrak_full->format('Y');

            $jadwal_kegiatan = Carbon::createFromFormat('Y-m-d', $r['kegiatan'][0]->tanggal_mulai);
            $tanggal_kegiatan = $jadwal_kegiatan->format('d');
            $bulan_kegiatan = NumberToWords::monthName($jadwal_kegiatan->format('m'));
            $tahun_kegiatan = $jadwal_kegiatan->format('Y');

            $list_petugas = $r['kegiatan'];
            $wilayah_tugas = $list_petugas[0]->wilayah_tugas;
            $nik = $list_petugas[0]->nik;
            $posisi_petugas = $list_petugas[0]->posisi_petugas;

            $data_honor = DB::table('wilayah_tugas')
                ->where('kode_wilayah', $wilayah_tugas)
                ->select('honor_pendataan', 'honor_pengolahan')
                ->first();

            if ($posisi_petugas === 'Mitra Pendataan') {
                $honor_max = $data_honor->honor_pendataan;
            } elseif ($posisi_petugas === 'Mitra Pengolahan') {
                $honor_max = $data_honor->honor_pengolahan;
            } elseif ($posisi_petugas === 'Mitra (Pendataan dan Pengolahan)') {
                $has_pengolahan = collect($list_petugas)->contains('tim_kerja_id', 1);
                $honor_max = $has_pengolahan ? $data_honor->honor_pengolahan : $data_honor->honor_pendataan;
            }

            dd($honor_max); // IGNORE

            $total_honor = array_sum(array_column($list_petugas, 'honor'));

            $total_honor_dibayar = ($total_honor > $honor_max) ? $honor_max : $total_honor;

            $total_honor_dibayar_terbilang = NumberToWords::toWords($total_honor_dibayar);

            $full_nomor_kontrak = str_pad($r['kegiatan'][0]->nomor_kontrak, 3, '0', STR_PAD_LEFT) . "/1201_MITRA/" . $tahun_kontrak;

            $data = [
                'bulan_kegiatan_kapital' => strtoupper($bulan_kegiatan),
                'tahun_kegiatan'         => $tahun_kegiatan,
                'nomor_kontrak'          => $full_nomor_kontrak,
                'hari'                   => $hari_kontrak,
                'tanggal_terbilang'      => ucfirst(NumberToWords::toWords((int) $tanggal_kontrak)),
                'bulan'                  => $bulan_kontrak,
                'tahun_terbilang'        => ucfirst(NumberToWords::toWords((int) $tahun_kontrak)),
                'nama_mitra'             => ucwords(strtolower($r['petugas'])),
                'pekerjaan'              => $list_petugas[0]->posisi_petugas,
                'alamat'                 => $list_petugas[0]->alamat,
                'bulan_kegiatan'         => $bulan_kegiatan,
                'total_honor'            => $total_honor_dibayar,
                'total_honor_terbilang'  => ucfirst($total_honor_dibayar_terbilang)
            ];

            $templateProcessor->setValues($data);

            dd($data);
            $templateProcessor->cloneRow('nama_kegiatan', count($list_petugas));
            foreach ($list_petugas as $index => $l) {
                $rowIndex = $index + 1;
                $templateProcessor->setValue("no#rowIndex", $rowIndex);
                $templateProcessor->setValue("nama_kegiatan#rowIndex" . ($index + 1), $l->nama_kegiatan);
                $templateProcessor->setValue("tanggal_mulai#rowIndex", $l['tanggal_mulai']);
                $templateProcessor->setValue("tanggal_selesai#rowIndex", $l['tanggal_selesai']);
                $templateProcessor->setValue("beban#rowIndex", $l['beban_kerja']);
                $templateProcessor->setValue("satuan#rowIndex", $l['satuan_beban_kerja']);
                $templateProcessor->setValue("honor#rowIndex", formatNominal($l['honor']));
                $templateProcessor->setValue("mata_anggaran#rowIndex", $l->kegiatan->beban_anggaran);
            }
            $output_path = storage_path('app/public/bast/KONTRAK_' . str_replace(' ', '_', $data['nama_mitra']) . '.docx');
            $templateProcessor->saveAs($output_path);
            $zip->addFile($output_path, 'KONTRAK_' . str_replace(' ', '_', $data['nama_mitra']) . '.docx');
            $kontrak_files[] = $output_path;
        }
        $zip->close();

        foreach ($kontrak_files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }
        return response()->download($zip_file_name)->deleteFileAfterSend(true);
    }
}
