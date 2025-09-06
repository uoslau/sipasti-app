<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Mitra>
 */
class MitraFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'nik' => $this->faker->unique()->numberBetween(1, 250),
            'nama_mitra' => $this->faker->name(),
            'posisi' => $this->faker->randomElement(['Mitra Pendataan', 'Mitra Pengolahan', 'Mitra (Pendataan dan Pengolahan)']),
            'email' => $this->faker->unique()->safeEmail(),
            'wilayah_id' => $this->faker->numberBetween(1, 2),
            'alamat' => $this->faker->address(),
            'tanggal_lahir' => $this->faker->date(),
            'npwp' => $this->faker->optional()->numerify('##.###.###.###.###'),
            'jenis_kelamin' => $this->faker->boolean(),
            'pekerjaan' => $this->faker->jobTitle(),
        ];
    }
}
