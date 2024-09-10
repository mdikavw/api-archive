<?php

namespace Database\Factories;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Reaction>
 */
class ReactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $reactableType = fake()->randomElement(['post', 'comment']);
        $type = fake()->randomElement(['favor', 'oppose']);
        $reactableIdMap = [
            'post' => Post::factory(),
            'comment' => Comment::factory()
        ];
        $reactableTypeMap = [
            'post' => Post::class,
            'comment' => Comment::class
        ];

        return [
            'user_id' => User::factory(),
            'reactable_id' => $reactableIdMap[$reactableType],
            'reactable_type' => $reactableTypeMap[$reactableType],
            'type' => $type,
        ];
    }
}
