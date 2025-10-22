<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class OfficialSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            'レフェリー','スコアラー','アシスタントスコアラー','ラインジャッジ', 'Jury/講師/審判長'
        ];

        foreach ($roles as $role) {
            DB::table('officials')->updateOrInsert([
                'name' => $role,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
