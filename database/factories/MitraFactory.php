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
            'nik' => $this->faker->unique()->numberBetween(1, 100),
            'nama_mitra' => $this->faker->name(),
            'posisi' => $this->faker->jobTitle(),
            'email' => $this->faker->unique()->safeEmail(),
            'alamat' => $this->faker->address(),
            'tanggal_lahir' => $this->faker->date(),
            'npwp' => $this->faker->optional()->numerify('##.###.###.###.###'),
            'jenis_kelamin' => $this->faker->boolean(),
            'pekerjaan' => $this->faker->jobTitle(),
        ];
    }
}
