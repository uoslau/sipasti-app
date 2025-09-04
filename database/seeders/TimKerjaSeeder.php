<?php

namespace Database\Seeders;

use App\Models\TimKerja;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class TimKerjaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $timKerjaData = [
            [
                'nama_tim_kerja' => 'Pengolahan, Metodologi dan Jaringan',
                'alias_tim_kerja' => 'PEMEJA',
            ],
            [
                'nama_tim_kerja' => 'Industri dan PEK',
                'alias_tim_kerja' => 'IPEK',
            ],
            [
                'nama_tim_kerja' => 'Survei Pertanian',
                'alias_tim_kerja' => 'SUPER',
            ],
            [
                'nama_tim_kerja' => 'Survei Perikanan, Peternakan dan Kehutanan',
                'alias_tim_kerja' => 'SP2K',
            ],
            [
                'nama_tim_kerja' => 'Statistik Kesejahteraan Rakyat',
                'alias_tim_kerja' => 'KESRA',
            ],
            [
                'nama_tim_kerja' => 'Statistik Kependudukan dan Ketenagakerjaan',
                'alias_tim_kerja' => 'SKK',
            ],
            [
                'nama_tim_kerja' => 'Statistik Ketahanan Sosial',
                'alias_tim_kerja' => 'HANSOS',
            ],
            [
                'nama_tim_kerja' => 'Distribusi dan Harga',
                'alias_tim_kerja' => 'DISHAR',
            ],
            [
                'nama_tim_kerja' => 'Keuangan, Teknologi, Informasi, dan Pariwisata',
                'alias_tim_kerja' => 'KTIP',
            ],
            [
                'nama_tim_kerja' => 'Neraca Pengeluaran dan Produksi',
                'alias_tim_kerja' => 'NEPPRO',
            ],
            [
                'nama_tim_kerja' => 'Analisis dan Pengembangan Statistik',
                'alias_tim_kerja' => 'APSTAT',
            ],
            [
                'nama_tim_kerja' => 'null',
                'alias_tim_kerja' => 'null',
            ]
        ];

        foreach ($timKerjaData as $data) {
            TimKerja::create($data);
        }
    }
}
