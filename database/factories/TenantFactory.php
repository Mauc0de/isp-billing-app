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
        $name = fake()->unique()->company();

        return [
            'name' => $name,
            'slug' => fake()->unique()->slug(),
            'email' => fake()->companyEmail(),
            'phone' => fake()->numerify('08##########'),
            'address' => fake()->address(),
            'timezone' => 'Asia/Jakarta',
            'is_active' => true,
        ];
    }
}
