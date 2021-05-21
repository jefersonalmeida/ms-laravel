<?php

namespace Tests\Feature\Http\Controllers\Api\Video;

use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

abstract class BaseVideoControllerTestCase extends TestCase
{
    use DatabaseMigrations;

    protected Video $model;
    protected array $sendData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = Video::factory()->create([
            'opened' => false,
        ]);

        /** @var Category $category */
        $category = Category::factory()->create();
        /** @var Genre $genre */
        $genre = Genre::factory()->create();
        $genre->categories()->sync([$category->id]);

        $this->sendData = [
            'title' => 'test_title',
            'description' => 'test_description',
            'year_launched' => 2020,
            'opened' => false,
            'rating' => Video::RATING_LIST[0],
            'duration' => 90,
            'categories_id' => [$category->id],
            'genres_id' => [$genre->id],
        ];
    }

    protected function model(): string
    {
        return Video::class;
    }

    protected function routeStore(): string
    {
        return route('videos.store');
    }

    protected function routeUpdate(): string
    {
        return route('videos.update', ['video' => $this->model->id]);
    }
}
