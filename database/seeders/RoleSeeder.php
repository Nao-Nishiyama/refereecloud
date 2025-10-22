<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('roles')->insert([
            ['name' => 'Admin',     'slug' => 'admin',     'rank' => 10,  'created_at'=>now(), 'updated_at'=>now()],
            ['name' => 'Committee', 'slug' => 'committee', 'rank' => 20,  'created_at'=>now(), 'updated_at'=>now()],
            ['name' => 'Chief',     'slug' => 'chief',     'rank' => 30,  'created_at'=>now(), 'updated_at'=>now()],
            ['name' => 'User',      'slug' => 'user',      'rank' => 100, 'created_at'=>now(), 'updated_at'=>now()],
        ]);
    }
}
