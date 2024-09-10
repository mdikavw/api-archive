<?php

namespace Database\Seeders;

use App\Models\PostStatus;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PostStatusSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        PostStatus::create([
            'name' => 'approved'
        ]);
        PostStatus::create([
            'name' => 'rejected'
        ]);
        PostStatus::create([
            'name' => 'pending'
        ]);
    }
}
