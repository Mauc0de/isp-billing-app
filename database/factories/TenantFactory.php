<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    public function definition(): array
    {
        return [
            'nama' => fake()->unique()->company(),
            'kode' => fake()->unique()->bothify('????##'),
            'alamat' => fake()->address(),
            'telepon' => fake()->numerify('08##########'),
            'aktif' => true,
        ];
    }
}
