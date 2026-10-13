<?php

namespace App\Services;

use App\Models\Kegiatan;
use App\Models\WilayahTugas;

class HonorCalculator
{
    /**
     * Honor seorang petugas pada satu kegiatan.
     *
     * Kegiatan O-B dibayar flat sesuai tarif wilayah, kegiatan biasa dikalikan
     * beban kerja. Tarif mengikuti wilayah tugas mitra (Nias / Nias Barat).
     */
    public function calculate(Kegiatan $kegiatan, int $bebanKerja, string $kodeWilayah): ?int
    {
        $tarif = $kodeWilayah === WilayahTugas::KODE_NIAS
            ? $kegiatan->honor_nias
            : $kegiatan->honor_nias_barat;

        if ($kegiatan->is_ob) {
            return $tarif === null ? null : (int) $tarif;
        }

        return (int) $tarif * $bebanKerja;
    }

    /**
     * Batas honor maksimal per bulan berdasarkan posisi mitra dan tarif wilayah.
     */
    public function cap(string $posisi, int $honorPendataan, int $honorPengolahan, bool $hasPengolahan = false): int
    {
        return match ($posisi) {
            'Mitra Pendataan' => $honorPendataan,
            'Mitra Pengolahan' => $honorPengolahan,
            'Mitra (Pendataan dan Pengolahan)' => $hasPengolahan ? $honorPengolahan : $honorPendataan,
            default => 0,
        };
    }
}
