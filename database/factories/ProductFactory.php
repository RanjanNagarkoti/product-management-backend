<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Product>
 */
class ProductFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'price' => rand(5, 1000000),
            'quantity' => rand(1, 20),
            'slug' => fake()->slug(),
            'thumbnail' => str_replace(storage_path('app'), '', fake()->image(storage_path('/app/images/products'), 1440, 640, 'human', true)),
            'status' => 1
        ];
    }
}
