<?php

namespace Database\Seeders;

use App\Models\PetugasKegiatan;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PetugasKegiatanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PetugasKegiatan::factory()->count(100)->create();
    }
}
