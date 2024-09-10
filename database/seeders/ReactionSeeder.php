<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Reaction;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ReactionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $lower = rand(1, User::count() - 10);
        $upper = $lower + 10;

        foreach (Post::all() as $post)
        {
            for ($i = $lower; $i < $upper; $i++)
            {
                Reaction::factory()->create([
                    'user_id' => $i,
                    'reactable_id' => $post->id,
                    'reactable_type' => Post::class
                ]);
            }
        }
        foreach (Comment::all() as $comment)
        {
            for ($i = $lower; $i < $upper; $i++)
            {
                Reaction::factory()->create([
                    'user_id' => $i,
                    'reactable_id' => $comment->id,
                    'reactable_type' => Comment::class
                ]);
            }
        }
    }
}
