<?php

namespace Database\Factories;

use App\Models\Mitra;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PetugasKegiatan>
 */
class PetugasKegiatanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $mitra = Mitra::inRandomOrder()->first();

        return [
            'nik' => $mitra->nik,
            'kegiatan_id' => $this->faker->numberBetween(1, 50),
            'bertugas_sebagai' => $this->faker->randomElement(['PCL']),
            'beban_kerja' => $this->faker->numberBetween(1, 10),
            'satuan_beban_kerja' => $this->faker->randomElement(['Dokumen']),
            'honor' => $this->faker->numberBetween(10000, 11000),
        ];
    }
}
