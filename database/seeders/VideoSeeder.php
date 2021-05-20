<?php

namespace Database\Seeders;

use App\Models\Genre;
use App\Models\Video;
use Illuminate\Database\Seeder;

class VideoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $genres = Genre::all();
        Video::factory()
            ->count(100)
            ->create()
            ->each(function (Video $video) use ($genres) {
                $subGenres = $genres->random(5)->load('categories');
                $categoryIds = [];
                foreach ($subGenres as $genre) {
                    array_push($categoryIds, ...$genre->categories()->pluck('id')->toArray());
                }
                $categoryIds = array_unique($categoryIds);
                $video->categories()->attach($categoryIds);
                $video->genres()->attach($subGenres->pluck('id')->toArray());
            });
    }
}
