<?php

namespace App\Http\Controllers;

use ZipArchive;
use Carbon\Carbon;
use App\Models\Kegiatan;
use Illuminate\Http\Request;
use App\Helpers\NumberToWords;
use App\Models\PetugasKegiatan;
use PhpOffice\PhpWord\TemplateProcessor;

class DownloadController extends Controller
{
    public function downloadBAST(Kegiatan $kegiatan)
    {
        if (!$kegiatan) {
            return response()->json(['error' => 'Kegiatan tidak ditemukan.'], 404);
        }

        if (!$kegiatan->is_generated) {
            return to_route('kegiatan.index')
                ->with('error', 'BAST belum bisa diunduh karena nomor belum digenerate.');
        }

        $petugas_kegiatan = PetugasKegiatan::join('nomor_kontraks', 'petugas_kegiatans.nik', '=', 'nomor_kontraks.nik')
            ->where('petugas_kegiatans.kegiatan_id', $kegiatan->id)
            ->select('petugas_kegiatans.*', 'nomor_kontraks.nomor_kontrak', 'nomor_kontraks.nomor_bast')
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

        // ambil tanggal kegiatan sebagai dasar penentuan tanggal kontrak
        // tanggal kontrak adalah tanggal 1 di bulan kegiatan dimulai
        // jika tanggal jatuh pada hari sabtu/minggu, maka tanggal kontrak adalah hari jumat di bulan sebelumnya
        $base_tanggal_kegiatan  = Carbon::createFromFormat('Y-m-d', $kegiatan->tanggal_mulai);
        $base_tanggal           = $base_tanggal_kegiatan->copy()->startOfMonth();
        $tanggal_kontrak_full   = ($base_tanggal->isSaturday() || $base_tanggal->isSunday())
            ? $base_tanggal->previousWeekday()
            : $base_tanggal;

        $tanggal_kontrak    = $tanggal_kontrak_full->format('d');
        $bulan_kontrak      = NumberToWords::monthName($tanggal_kontrak_full->format('m'));
        $tahun_kontrak      = $tanggal_kontrak_full->format('Y');

        $tanggal_kegiatan   = $base_tanggal_kegiatan->format('d');
        $bulan_kegiatan     = NumberToWords::monthName($base_tanggal_kegiatan->format('m'));
        $tahun_kegiatan     = $base_tanggal_kegiatan->format('Y');

        $tanggal_kegiatan_selesai   = Carbon::createFromFormat('Y-m-d', $kegiatan->tanggal_selesai);
        $hari_bast                  = NumberToWords::dayName($tanggal_kegiatan_selesai->format('l'));
        $tanggal_bast               = $tanggal_kegiatan_selesai->format('d');
        $tanggal_bast_terbilang     = ucfirst(NumberToWords::toWords((int) $tanggal_bast));
        $bulan_bast                 = NumberToWords::monthName($tanggal_kegiatan_selesai->format('m'));
        $tahun_bast                 = $tanggal_kegiatan_selesai->format('Y');
        $tahun_bast_terbilang       = ucfirst(NumberToWords::toWords((int) $tahun_bast));

        $bast_files = [];

        foreach ($petugas_kegiatan as $p) {
            $templateProcessor = new TemplateProcessor($template_path);

            $full_nomor_bast = str_pad($p->nomor_bast, 3, '0', STR_PAD_LEFT) . "/1201_BAST/" . $tahun_bast;
            $full_nomor_kontrak = str_pad($p->nomor_kontrak, 3, '0', STR_PAD_LEFT) . "/1201_MITRA/" . $tahun_kontrak;

            $data = [
                'bulan_kegiatan_kapital'    => strtoupper($bulan_kegiatan),
                'tahun_kegiatan'            => $tahun_kegiatan,
                'nomor_bast'                => $full_nomor_bast,
                'hari'                      => $hari_bast,
                'tanggal_terbilang'         => $tanggal_bast_terbilang,
                'bulan'                     => $bulan_bast,
                'tahun_terbilang'           => $tahun_bast_terbilang,
                'nama_mitra'                => ucwords(strtolower($p->mitra->nama_mitra)),
                'alamat'                    => $p->mitra->alamat,
                'bulan_kegiatan'            => $bulan_kegiatan,
                'tanggal_kegiatan'          => $tanggal_kegiatan,
                'nomor_kontrak'             => $full_nomor_kontrak,
                'tanggal_kontrak'           => $tanggal_kontrak,
                'bulan_kontrak'             => $bulan_kontrak,
                'tahun_kontrak'             => $tahun_kontrak,
                'nama_kegiatan'             => $kegiatan->nama_kegiatan,
                'beban'                     => $p->beban_kerja,
                'satuan'                    => $p->satuan_beban_kerja,
                'tim_kerja'                 => $p->kegiatan->timKerja->alias_tim_kerja,
            ];

            foreach ($data as $key => $value) {
                $templateProcessor->setValue($key, $value);
            }

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
}
