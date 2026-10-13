<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Kegiatan;
use App\Models\NomorKontrak;

class NomorKontrakService
{
    /**
     * Generate nomor SPK (kontrak) dan BAST untuk seluruh petugas pada satu kegiatan.
     *
     * Nomor kontrak dipakai ulang bila petugas sudah punya kontrak di bulan & tahun
     * yang sama; nomor BAST selalu increment unik per tahun.
     *
     * @return bool true bila nomor berhasil digenerate, false bila belum ada petugas
     */
    public function generate(Kegiatan $kegiatan): bool
    {
        $tahun = Carbon::parse($kegiatan->tanggal_mulai)->format('Y');
        $bulan = Carbon::parse($kegiatan->tanggal_mulai)->format('m');

        $petugas_kegiatan = $kegiatan->petugasKegiatan()->get();

        if ($petugas_kegiatan->isEmpty()) {
            return false;
        }

        // ambil nomor kontrak terakhir dalam satu tahun
        $last_nomor_kontrak = NomorKontrak::where('tahun', $tahun)
            ->max('nomor_kontrak') ?? 0;

        // untuk nomor bast, selalu increment (unik) dari nomor bast terakhir di tahun yang sama
        $last_nomor_bast = NomorKontrak::where('tahun', $tahun)
            ->max('nomor_bast') ?? 0;

        foreach ($petugas_kegiatan as $p) {
            // cek kontrak petugas di bulan dan tahun yang sama
            // 1 petugas hanya boleh punya 1 kontrak di bulan yang sama
            $cek_kontrak_petugas = NomorKontrak::where('nik', $p->nik)
                ->where('tahun', $tahun)
                ->where('bulan', $bulan)
                ->first();

            // jika sudah ada, gunakan nomor kontrak yang sama
            if ($cek_kontrak_petugas) {
                $ins_nomor_kontrak = $cek_kontrak_petugas->nomor_kontrak;
            } else {
                $last_nomor_kontrak++;
                $ins_nomor_kontrak = $last_nomor_kontrak;
            }

            $last_nomor_bast++;
            $ins_nomor_bast = $last_nomor_bast;

            NomorKontrak::create([
                'nik'           => $p->nik,
                'kegiatan_id'   => $kegiatan->id,
                'tahun'         => $tahun,
                'bulan'         => $bulan,
                'nomor_kontrak' => $ins_nomor_kontrak,
                'nomor_bast'    => $ins_nomor_bast,
            ]);
        }

        $kegiatan->is_generated = true;
        $kegiatan->save();

        return true;
    }
}
