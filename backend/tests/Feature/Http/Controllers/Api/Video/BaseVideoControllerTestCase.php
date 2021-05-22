<?php

namespace Tests\Feature\Http\Controllers\Api\Video;

use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
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
            'thumb_file' => 'thumb_file.jpg',
            'banner_file' => 'banner_file.jpg',
            'trailer_file' => 'trailer_file.mp4',
            'video_file' => 'video_file.mp4',
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

    protected function assertIfFilesUrlExists(Video $model, TestResponse $response)
    {
        $fileFields = Video::fileFields();
        $data = $response->json('data');
        $data = array_key_exists(0, $data) ? $data[0] : $data;
        foreach ($fileFields as $field) {
            $file = $model->{$field};
            $this->assertEquals(
                Storage::url($model->relativeFilePath($file)),
                $data[sprintf('%s_url', $field)]
            );
        }
    }
}
