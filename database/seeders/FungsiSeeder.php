<?php

namespace Database\Seeders;

use App\Models\Fungsi;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class FungsiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $fungsiData = [
            [
                'fungsi' => 'Distribusi',
            ],
            [
                'fungsi' => 'IPDS',
            ],
            [
                'fungsi' => 'NWAS',
            ],
            [
                'fungsi' => 'Produksi',
            ],
            [
                'fungsi' => 'Sosial',
            ],
            [
                'fungsi' => 'null',
            ],
        ];

        foreach ($fungsiData as $data) {
            Fungsi::create($data);
        }
    }
}
