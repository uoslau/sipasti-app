<?php

namespace App\Http\Controllers;

use App\Models\Kegiatan;
use App\Models\NomorKontrak;
use Illuminate\Http\Request;
use App\Models\PetugasKegiatan;
use Illuminate\Support\Facades\DB;

class NomorKontrakController extends Controller
{
    public function generate(Kegiatan $kegiatan)
    {
        if ($kegiatan->is_generated) {
            return to_route('kegiatan.edit', $kegiatan->slug)
                ->with('error', 'Nomor SPK dan BAST sudah pernah digenerate.');
        }

        DB::beginTransaction();

        try {
            $tahun = \Carbon\Carbon::parse($kegiatan->tanggal_mulai)->format('Y');
            $bulan = \Carbon\Carbon::parse($kegiatan->tanggal_mulai)->format('m');

            $petugas_kegiatan = $kegiatan->petugasKegiatan()->get();

            if ($petugas_kegiatan->isEmpty()) {
                DB::rollBack();
                return to_route('kegiatan.edit', $kegiatan->slug)
                    ->with('error', 'Belum ada petugas di kegiatan ini!');
            }

            // ambil nomor kontrak terakhir dalam satu tahun
            $last_nomor_kontrak = NomorKontrak::where('tahun', $tahun)
                ->max('nomor_kontrak') ?? 0;

            // untuk nomor bast, selalu increment (unik) dari nomor bast terakhir di tahun yang sama
            $last_nomor_bast = NomorKontrak::where('tahun', $tahun)
                ->max('nomor_bast') ?? 0;

            foreach ($petugas_kegiatan as $p) {
                // cek kontrak petugas di bulan dan tahun yang sama
                // 1 peutgas hanya boleh punya 1 kontrak di bulan yang sama
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
                    'nik'                   => $p->nik,
                    'kegiatan_id'           => $kegiatan->id,
                    'tahun'                 => $tahun,
                    'bulan'                 => $bulan,
                    'nomor_kontrak'         => $ins_nomor_kontrak,
                    'nomor_bast'            => $ins_nomor_bast,
                ]);
            }

            $kegiatan->is_generated = true;
            $kegiatan->save();

            DB::commit();

            return to_route('kegiatan.edit', $kegiatan->slug)
                ->with('success', 'Nomor Kontrak dan BAST berhasil digenerate.');
        } catch (\Throwable $th) {
            DB::rollBack();
            return to_route('kegiatan.edit', $kegiatan->slug)
                ->with('error', 'Terjadi kesalahan: ' . $th->getMessage());
        }
    }
}
