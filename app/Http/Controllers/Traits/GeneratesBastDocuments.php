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
     * @param string $templatePath Path absolut ke file template .docx
     * @return string Path absolut ke file ZIP yang dihasilkan
     */
    protected function generateAndZipBastDocuments(Kegiatan $kegiatan, $petugas_kegiatan, $templatePath)
    {
        $zip = new ZipArchive();
        $zip_file_name = storage_path('app/public/bast/BAST_' . str_replace(' ', '_', $kegiatan->nama_kegiatan) . '.zip');

        if ($zip->open($zip_file_name, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            // Jika gagal, lempar exception atau tangani error
            throw new \Exception('Gagal membuat file zip.');
        }

        $tempDirectory = storage_path('app/public/temp');
        if (!is_dir($tempDirectory)) {
            // Buat direktori jika belum ada. 
            // Parameter ketiga (true) memungkinkan pembuatan direktori secara rekursif.
            mkdir($tempDirectory, 0755, true);
        }

        $dateVars = $this->prepareDateVariables($kegiatan);
        $bast_files = []; // Untuk melacak file DOCX yang dibuat agar bisa dihapus

        foreach ($petugas_kegiatan as $p) {
            $templateProcessor = new TemplateProcessor($templatePath);

            $full_nomor_bast = str_pad($p->nomor_bast, 3, '0', STR_PAD_LEFT) . "/1201_BAST/" . $dateVars['tahun_bast'];
            $full_nomor_kontrak = str_pad($p->nomor_kontrak, 3, '0', STR_PAD_LEFT) . "/1201_MITRA/" . $dateVars['tahun_kontrak'];

            // ... (Semua data array Anda sama persis) ...
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

            $output_path = storage_path('app/public/temp/BAST_' . str_replace(' ', '_', $data['nama_mitra']) . '.docx');
            $templateProcessor->saveAs($output_path);

            $zip->addFile($output_path, 'BAST_' . str_replace(' ', '_', $data['nama_mitra']) . '.docx');
            $bast_files[] = $output_path;
        }

        $zip->close();

        // Hapus file-file DOCX individual setelah dimasukkan ke dalam zip
        foreach ($bast_files as $file) {
            if (file_exists($file)) {
                unlink($file);
            }
        }

        // Kembalikan path file ZIP yang sudah jadi
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
