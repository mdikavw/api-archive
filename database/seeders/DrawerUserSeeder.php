<?php

namespace Database\Seeders;

use App\Models\Drawer;
use App\Models\DrawerUser;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DrawerUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $drawers = Drawer::all();
        foreach ($drawers as $drawer)
        {
            DrawerUser::factory(rand(10, 20))->create([
                'drawer_id' => $drawer->id
            ]);
        }
    }
}
