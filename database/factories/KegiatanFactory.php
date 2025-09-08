<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Kegiatan>
 */
class KegiatanFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nama_kegiatan' => $this->faker->sentence(3),
            'tanggal_mulai' => $this->faker->dateTimeBetween('-1 month', '-1 month'),
            'tanggal_selesai' => $this->faker->dateTimeBetween('-1 month', '-1 months'),
            'beban_anggaran' => $this->faker->sentence(1),
            'tim_kerja_id' => $this->faker->numberBetween(1, 11),
            'fungsi_id' => $this->faker->numberBetween(6, 6),
            'honor_nias' => $this->faker->numberBetween(100000, 1000000),
            'honor_nias_barat' => $this->faker->numberBetween(100000, 1000000),
        ];
    }
}
