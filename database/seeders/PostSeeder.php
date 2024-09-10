<?php

namespace Database\Seeders;

use App\Models\Drawer;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all();
        $drawers = Drawer::all();
        Post::factory(20)->recycle([$users, $drawers])->create();
    }
}
