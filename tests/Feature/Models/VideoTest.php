<?php

namespace Tests\Feature\Models;

use App\Models\Video;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Tests\TestCase;
use Throwable;

class VideoTest extends TestCase
{
    use DatabaseMigrations;

    public function testList()
    {
        Video::factory()->count(1)->create();
        $collection = Video::all();
        $this->assertCount(1, $collection);
        $keys = array_keys($collection->first()->getAttributes());
        $this->assertEqualsCanonicalizing([
            'id',
            'title',
            'description',
            'year_launched',
            'opened',
            'rating',
            'duration',
            'created_at',
            'updated_at',
            'deleted_at'
        ], $keys);
    }

    public function testCreate()
    {
        $data = [
            'title' => 'Title',
            'description' => 'Description',
            'year_launched' => 2021,
            'rating' => Video::RATING_LIST[0],
            'duration' => 90,
        ];

        $model = Video::create($data);
        $model->refresh();

        $this->assertTrue(Str::isUuid($model->id));
        $this->assertEquals($data['title'], $model->title);
        $this->assertFalse($model->opened);

        $model = Video::create($data + ['opened' => true]);
        $this->assertTrue($model->opened);
    }

    public function testUpdate()
    {
        /** @var Video $model */
        $model = Video::factory()->create();

        $data = [
            'title' => 'Title',
            'description' => 'Description',
            'year_launched' => 2021,
            'opened' => true,
            'rating' => Video::RATING_LIST[0],
            'duration' => 90,
        ];
        $model->update($data);

        foreach ($data as $key => $item) {
            $this->assertEquals($item, $model->{$key});
        }
    }

    public function testDeleteAndRestore()
    {
        /** @var Video $model */
        $model = Video::factory()->create();
        $model->delete();
        $this->assertNull(Video::find($model->id));

        $model->restore();
        $this->assertNotNull(Video::find($model->id));
    }

    public function testRollbackCreate()
    {
        $hasError = false;
        try {
            Video::create([
                'title' => 'test_title',
                'description' => 'test_description',
                'year_launched' => 2020,
                'opened' => false,
                'rating' => Video::RATING_LIST[0],
                'duration' => 90,
                'category_ids' => [0, 1, 2]
            ]);
        } catch (QueryException $exception) {
            $this->assertCount(0, Video::all());
            $hasError = true;
        }
        $this->assertTrue($hasError);
    }

    public function testRollbackUpdate()
    {
        /** @var Video $model */
        $model = Video::factory()->create();
        $title = $model->title;
        $hasError = false;
        try {
            $model->update([
                'title' => 'test_title',
                'description' => 'test_description',
                'year_launched' => 2020,
                'opened' => false,
                'rating' => Video::RATING_LIST[0],
                'duration' => 90,
                'category_ids' => [0, 1, 2]
            ]);
        } catch (QueryException | Throwable $exception) {
            $this->assertDatabaseHas('videos', ['title' => $title]);
            $hasError = true;
        }
        $this->assertTrue($hasError);
    }
}
