<?php

namespace Database\Seeders;

use App\Models\Drawer;
use Database\Factories\DrawerFactory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DrawerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Drawer::factory(10)->create();
    }
}
