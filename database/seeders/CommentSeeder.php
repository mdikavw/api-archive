<?php

namespace Database\Seeders;

use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class CommentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $posts = Post::all();
        Comment::factory()
            ->count(100)
            ->recycle($posts)
            ->create([
                'parent_id' => null
            ]);

        $comments = Comment::all();

        Comment::factory(1000)
            ->recycle([$users, $posts, $comments])
            ->create(
                [
                    'parent_id' => Comment::factory()
                ]
            );
    }
}
