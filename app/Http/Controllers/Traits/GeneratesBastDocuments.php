<?php

namespace App\Http\Controllers\Traits;

use ZipArchive;
use Carbon\Carbon;
use App\Models\Kegiatan;
use App\Helpers\NumberToWords;
use PhpOffice\PhpWord\TemplateProcessor;

trait GeneratesBastDocuments
{
    /**
     * Mesin utama untuk generate file BAST dari template dan data, lalu mengemasnya dalam ZIP.
     *
     * @param Kegiatan $kegiatan
     * @param \Illuminate\Support\Collection $petugas_kegiatan
     * @param string $template_path Path absolut ke file template .docx
     * @return string Path absolut ke file ZIP yang dihasilkan
     */
    protected function generateAndZipBastDocuments(Kegiatan $kegiatan, $petugas_kegiatan, $template_path)
    {
        $zip = new ZipArchive();

        if (!$kegiatan->is_ob) {
            $zip_file_name = storage_path('app/public/bast/BAST_' . str_replace(' ', '_', $kegiatan->nama_kegiatan) . '.zip');
            $outputPath = 'app/public/bast/BAST_';
            $prefix = 'BAST_';
        } else {
            $zip_file_name = storage_path('app/public/ob/OB_' . str_replace(' ', '_', $kegiatan->nama_kegiatan) . '.zip');
            $outputPath = 'app/public/ob/OB_';
            $prefix = 'OB_';
        }

        if ($zip->open($zip_file_name, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            return to_route('kegiatan.edit', $kegiatan->slug)
                ->with('error', 'Gagal membuat file zip.');
        }

        $dateVars = $this->prepareDateVariables($kegiatan);

        $word_files = [];

        foreach ($petugas_kegiatan as $p) {
            $templateProcessor = new TemplateProcessor($template_path);

            $nomor_bast         = str_pad($p->nomor_bast, 3, '0', STR_PAD_LEFT);
            $full_nomor_bast    = $nomor_bast . "/1201_BAST/" . $dateVars['tahun_bast'];

            $nomor_kontrak      = str_pad($p->nomor_kontrak, 3, '0', STR_PAD_LEFT);
            $full_nomor_kontrak = $nomor_kontrak . "/1201_MITRA/" . $dateVars['tahun_kontrak'];

            $data = [
                'bulan_kapital'          => strtoupper($dateVars['bulan_kegiatan']),
                'tahun_kegiatan'         => $dateVars['tahun_kegiatan'],
                'no_bast'                => $nomor_bast,
                'nomor_bast'             => $full_nomor_bast,
                'hari'                   => $dateVars['hari_bast'],
                'tanggal_terbilang'      => $dateVars['tanggal_bast_terbilang'],
                'bulan'                  => $dateVars['bulan_bast'],
                'tahun_terbilang'        => $dateVars['tahun_bast_terbilang'],
                'nama_mitra'             => ucwords(strtolower($p->nama_mitra)),
                'alamat'                 => $p->alamat,
                'bulan_kegiatan'         => $dateVars['bulan_kegiatan'],
                'tanggal_kegiatan'       => $dateVars['tanggal_kegiatan'],
                'no_kontrak'             => $nomor_kontrak,
                'nomor_kontrak'          => $full_nomor_kontrak,
                'tanggal_kontrak'        => $dateVars['tanggal_kontrak'],
                'bulan_kontrak'          => $dateVars['bulan_kontrak'],
                'tahun_kontrak'          => $dateVars['tahun_kontrak'],
                'nama_kegiatan'          => $kegiatan->nama_kegiatan,
                'beban'                  => $p->beban_kerja,
                'satuan'                 => ucwords(strtolower($p->satuan_beban_kerja)),
                'tim_kerja'              => $p->alias_tim_kerja,
            ];

            $templateProcessor->setValues($data);

            $file_name = str_replace(' ', '_', $data['nama_mitra']) . '.docx';

            $output_path = storage_path($outputPath . $file_name);

            $templateProcessor->saveAs($output_path);

            $zip->addFile($output_path, $prefix . $file_name);

            $word_files[] = $output_path;
        }

        if ($zip->close() === false) {
            throw new \Exception('Gagal menyelesaikan pembuatan file zip. Periksa izin folder atau file zip yang mungkin terkunci.');
        }

        foreach ($word_files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }

        return $zip_file_name;
    }

    /**
     * 
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
}
