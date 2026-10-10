<?php

namespace Database\Factories;

use App\Models\Buku;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Buku>
 */
class BukuFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'isbn'         => fake()->unique()->isbn13(),
            'judul'        => fake()->sentence(3),
            'penulis'      => fake()->name(),
            'penerbit'     => fake()->company(),
            'tahun_terbit' => fake()->numberBetween(2015, 2026),
            'stok'         => fake()->numberBetween(1, 50),
            'sinopsis'     => fake()->paragraph(),
        ];
    }
}
