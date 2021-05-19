<?php

namespace Database\Factories;

use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\Factory;

class VideoFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Video::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence(3),
            'description' => $this->faker->sentence(10),
            'year_launched' => rand(1895, 2021),
            'opened' => rand(false, true),
            'rating' => $this->faker->randomElement(Video::RATING_LIST),
            'duration' => rand(1, 30),
            // 'thumb_file' => null,
            // 'banner_file' => null,
            // 'trailer_file' => null,
            // 'video' => null,
            // 'published' => rand(false, true)
        ];
    }
}
