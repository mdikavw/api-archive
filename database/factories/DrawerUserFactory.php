<?php

namespace Database\Factories;

use App\Models\Drawer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\DrawerUser>
 */
class DrawerUserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'drawer_id' => Drawer::factory(),
            'role_id' => fake()->randomElement([2, 3])
        ];
    }
}
