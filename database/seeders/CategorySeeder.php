<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class CategorySeeder extends Seeder
{
    private $category;

    public function __construct(Category $category)
    {
        $this->category = $category;
    }

    /**
     * Run the database seeds.
     * 2D associative array for multiple records
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Instrument', 'created_at' => NOW(), 'updated_at' => NOW()],
            ['name' => 'Place', 'created_at' => NOW(), 'updated_at' => NOW()]
        ];

        $this->category->insert($categories);
    }
}
