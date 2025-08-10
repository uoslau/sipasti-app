<?php

namespace App\Imports;

use App\Models\Mitra;
use App\Models\Kegiatan;
use App\Models\PetugasKegiatan;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

class PetugasImport implements ToCollection
{
    /**
     * @param Collection $collection
     */

    protected $kegiatan_id;

    public function __construct($kegiatan_id)
    {
        $this->kegiatan_id = $kegiatan_id;
    }

    public function collection(Collection $collection)
    {
        $kegiatan = Kegiatan::find($this->kegiatan_id);

        $index = 1;
        $processedNik = [];

        foreach ($collection as $row) {
            if ($index > 1) {
                if (empty($row[1]) && empty($row[2])) {
                    $index++;
                    continue;
                }

                $nik = !empty($row[1]) ? $row[1] : '';
                if (empty($nik)) {
                    $index++;
                    continue;
                }

                if (in_array($nik, $processedNik)) {
                    return to_route('kegiatan.edit', $kegiatan->slug)
                        ->with('error', 'Terdapat NIK yang sama di file excel pada baris ke-' . $index);
                }

                $processedNik[] = $nik;

                $cek_mitra = PetugasKegiatan::where('kegiatan_id', $kegiatan->id)
                    ->where('nik', $nik)
                    ->exists();

                $mitra = Mitra::where('nik', $nik)->first();

                if (!$mitra) {
                    return to_route('kegiatan.edit', $kegiatan->slug)
                        ->with('error', 'NIK ' . $nik . ' pada baris ke-' . $index . ' tidak ditemukan di database Mitra!');
                }

                if ($cek_mitra) {
                    return to_route('kegiatan.edit', $kegiatan->slug)
                        ->with('error', ucwords(strtolower($mitra->nama_mitra)) . ' sudah terdaftar di kegiatan ini! Silahkan cek File Excel yang diimport!');
                }

                $bertugas_sebagai   = !empty($row[3]) ? $row[3] : '';

                if (!($row[4] == '1201' || $row[4] == '1225')) {
                    return to_route('kegiatan.edit', $kegiatan->slug)
                        ->with('error', 'Terdapat kesalahan, pastikan wilayah_tugas di file excel bernilai 1201 atau 1225');
                }

                $wilayah_tugas          = !empty($row[4]) ? $row[4] : '';
                $beban_kerja            = !empty($row[5]) ? $row[5] : '';
                $satuan_beban_kerja     = !empty($row[6]) ? $row[6] : '';
                $honor                  = ($wilayah_tugas == "1201") ? ($kegiatan->honor_nias * (int)$beban_kerja) : ($wilayah_tugas == "1225" ? ($kegiatan->honor_nias_barat * (int)$beban_kerja) : 0);

                $data = [
                    'nik'                   => $nik,
                    'nama_mitra'            => $mitra->nama_mitra,
                    'kegiatan_id'           => $kegiatan->id,
                    'bertugas_sebagai'      => $bertugas_sebagai,
                    'wilayah_tugas'         => $wilayah_tugas,
                    'beban_kerja'           => (int)$beban_kerja,
                    'satuan_beban_kerja'    => $satuan_beban_kerja,
                    'honor'                 => $honor,
                ];
                PetugasKegiatan::create($data);
            }
            $index++;
        }
        return to_route('kegiatan.edit', $kegiatan->slug)->with('success', 'Petugas berhasil diimport!');
    }
}
