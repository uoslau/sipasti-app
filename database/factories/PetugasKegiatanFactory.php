<?php

namespace Database\Factories;

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
        return [
            'nik' => $this->faker->unique()->numberBetween(1, 100),
            'kegiatan_id' => $this->faker->numberBetween(1, 19),
            'bertugas_sebagai' => $this->faker->jobTitle(),
            'wilayah_tugas' => $this->faker->city(),
            'beban_kerja' => $this->faker->numberBetween(1, 100),
            'satuan_beban_kerja' => $this->faker->word(),
            'honor' => $this->faker->numberBetween(100000, 1000000),
        ];
    }
}
