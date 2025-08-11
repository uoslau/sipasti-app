<?php

namespace App\Imports;

use App\Models\Mitra;
use App\Models\Kegiatan;
use Illuminate\Support\Arr;
use App\Models\PetugasKegiatan;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
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
        $kegiatan = Kegiatan::findOrFail($this->kegiatan_id);
        $processedNik = [];
        $errors = [];

        DB::beginTransaction();

        try {
            foreach ($collection as $index => $row) {
                if ($index === 0) continue;

                $nik                = trim(Arr::get($row, 1, ''));
                $bertugas_sebagai   = trim(Arr::get($row, 3, ''));
                $wilayah_tugas      = trim(Arr::get($row, 4, ''));
                $beban_kerja        = (int) Arr::get($row, 5, 0);
                $satuan_beban       = trim(Arr::get($row, 6, ''));

                $validator = Validator::make([
                    'nik'              => $nik,
                    'bertugas_sebagai' => $bertugas_sebagai,
                    'wilayah_tugas'    => $wilayah_tugas,
                    'beban_kerja'      => $beban_kerja,
                    'satuan_beban'     => $satuan_beban,
                ], [
                    'nik'              => 'required|exists:mitras,nik',
                    'bertugas_sebagai' => 'required|string|max:255|regex:/^[a-z A-Z]+$/',
                    'wilayah_tugas'    => 'required|in:1201,1225',
                    'beban_kerja'      => 'required|integer|min:1',
                    'satuan_beban'     => 'required|string|max:255|regex:/^[a-z A-Z]+$/',
                ]);

                if ($validator->fails()) {
                    $errors[] = "Baris ke-" . ($index + 1) . ": " . implode(', ', $validator->errors()->all());
                    continue;
                }

                if (in_array($nik, $processedNik)) {
                    $errors[] = "Baris ke-" . ($index + 1) . ": NIK {$nik} duplikat di file Excel.";
                    continue;
                }

                $processedNik[] = $nik;

                $nama_mitra = Mitra::where('nik', $nik)->get('nama_mitra')->first();

                $cek_mitra = PetugasKegiatan::where('kegiatan_id', $kegiatan->id)
                    ->where('nik', $nik)
                    ->exists();
                if ($cek_mitra) {
                    $errors[] = "Baris ke-" . ($index + 1) . ": {$nama_mitra} sudah terdaftar di kegiatan ini.";
                    continue;
                }

                $honor = $wilayah_tugas == "1201"
                    ? $kegiatan->honor_nias * $beban_kerja
                    : $kegiatan->honor_nias_barat * $beban_kerja;

                PetugasKegiatan::create([
                    'nik'                => $nik,
                    'nama_mitra'         => $nama_mitra,
                    'kegiatan_id'        => $kegiatan->id,
                    'bertugas_sebagai'   => $bertugas_sebagai,
                    'wilayah_tugas'      => $wilayah_tugas,
                    'beban_kerja'        => $beban_kerja,
                    'satuan_beban_kerja' => $satuan_beban,
                    'honor'              => $honor,
                ]);
            }

            if (!empty($errors)) {
                DB::rollBack();
                return to_route('kegiatan.edit', $kegiatan->slug)
                    ->with('error', implode('<br>', $errors));
            }

            DB::commit();
            return to_route('kegiatan.edit', $kegiatan->slug)
                ->with('success', 'Petugas berhasil diimport!');
        } catch (\Throwable $th) {
            DB::rollBack();
            return to_route('kegiatan.edit', $kegiatan->slug)
                ->with('error', 'Terjadi kesalahan: ' . $th->getMessage());
        }
    }
}
