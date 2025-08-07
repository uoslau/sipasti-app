<?php

namespace Database\Seeders;

use App\Models\WilayahTugas;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class WilayahTugasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        WilayahTugas::create([
            'kode_wilayah' => '1201',
            'nama_wilayah' => 'Nias',
            'honor_pendataan' => 3627000,
            'honor_pengolahan' => 3355000,
        ]);

        WilayahTugas::create([
            'kode_wilayah' => '1225',
            'nama_wilayah' => 'Nias Barat',
            'honor_pendataan' => 3704000,
            'honor_pengolahan' => 3426000,
        ]);
    }
}
