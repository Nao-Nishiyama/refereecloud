<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class LicenseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $licenses = [
            'IR','AIR','A','B','C','H','その他'
        ];

        foreach ($licenses as $license) {
            DB::table('licenses')->updateOrInsert([
                'name' => $license,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
