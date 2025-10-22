<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class TypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $types = [
            '６人制','９人制','ビーチ','ソフトバレー', 'その他'
        ];

        foreach ($types as $type) {
            DB::table('types')->updateOrInsert([
                'name' => $type,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

    }
}
