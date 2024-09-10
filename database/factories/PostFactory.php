<?php

namespace Database\Factories;

use App\Models\Drawer;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

use function PHPSTORM_META\type;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Post>
 */
class PostFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $type = fake()->randomElement(['drawer', 'profile']);
        return [
            'user_id' => User::factory(),
            'title' => fake()->sentence(),
            'content' => fake()->paragraphs(3, true),
            'slug' => fake()->unique()->slug(),
            'type' => $type,
            'drawer_id' => $type == 'drawer' ? Drawer::factory() : null
        ];
    }
}
