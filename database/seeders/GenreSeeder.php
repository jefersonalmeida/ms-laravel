<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Genre;
use Illuminate\Database\Seeder;

class GenreSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $categories = Category::all();
        Genre::factory()
            ->count(100)
            ->create()
            ->each(function (Genre $genre) use ($categories) {
                $categoryIds = $categories->random(5)->pluck('id')->toArray();
                $genre->categories()->attach($categoryIds);
            });
    }
}
