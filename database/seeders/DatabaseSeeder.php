<?php

namespace Database\Seeders;

use App\Models\User;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(PrefectureSeeder::class);
        $this->call(LicenseSeeder::class);
        $this->call(OfficialSeeder::class);
        $this->call(CategorySeeder::class);
        $this->call(TypeSeeder::class);
        $this->call(OrganizationSeeder::class);
        $this->call(RoleSeeder::class);

    }
}
