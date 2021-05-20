<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Tests\TestCase;
use Throwable;

class VideoTest extends TestCase
{
    use DatabaseMigrations;

    private array $data;

    protected function setUp(): void
    {
        parent::setUp();
        $this->data = [
            'title' => 'test_title',
            'description' => 'test_description',
            'year_launched' => 2020,
            'rating' => Video::RATING_LIST[0],
            'duration' => 90,
        ];
    }

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

    public function testCreateWithBasicFields()
    {
        $model = Video::create($this->data);
        $model->refresh();

        $this->assertTrue(Str::isUuid($model->id));
        $this->assertEquals($this->data['title'], $model->title);
        $this->assertFalse($model->opened);
        $this->assertDatabaseHas('videos', $this->data + ['opened' => false]);

        $model = Video::create($this->data + ['opened' => true]);
        $this->assertTrue($model->opened);
        $this->assertDatabaseHas('videos', $this->data + ['opened' => true]);
    }

    public function testCreateWithRelations()
    {
        $category = Category::factory()->create();
        $genre = Genre::factory()->create();
        $model = Video::create(
            $this->data + [
                'category_ids' => [$category->id],
                'genre_ids' => [$genre->id],
            ]);

        $this->assertHasCategory($model->id, $category->id);
        $this->assertHasGenre($model->id, $genre->id);
    }

    public function testUpdateWithBasicFields()
    {
        /** @var Video $model */
        $model = Video::factory()->create([
            'opened' => false
        ]);
        $model->update($this->data);
        $this->assertFalse($model->opened);
        $this->assertDatabaseHas('videos', $this->data + ['opened' => false]);

        /** @var Video $model */
        $model = Video::factory()->create([
            'opened' => false
        ]);
        $model->update($this->data + ['opened' => true]);
        $this->assertTrue($model->opened);
        $this->assertDatabaseHas('videos', $this->data + ['opened' => true]);
    }

    public function testUpdateWithRelations()
    {
        /** @var Category $category */
        $category = Category::factory()->create();
        /** @var Genre $genre */
        $genre = Genre::factory()->create();
        /** @var Video $model */
        $model = Video::factory()->create();
        $model->update(
            $this->data + [
                'category_ids' => [$category->id],
                'genre_ids' => [$genre->id],
            ]);

        $this->assertHasCategory($model->id, $category->id);
        $this->assertHasGenre($model->id, $genre->id);
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

    public function testHandleRelations()
    {
        /** @var Video $model */
        $model = Video::factory()->create();
        Video::handleRelations($model, []);
        $this->assertCount(0, $model->categories);
        $this->assertCount(0, $model->genres);

        /** @var Category $category */
        $category = Category::factory()->create();
        Video::handleRelations($model, ['category_ids' => [$category->id]]);
        $model->refresh();
        $this->assertCount(1, $model->categories);

        /** @var Genre $genre */
        $genre = Genre::factory()->create();
        Video::handleRelations($model, ['genre_ids' => [$genre->id]]);
        $model->refresh();
        $this->assertCount(1, $model->genres);

        $model->categories()->detach();
        $model->genres()->detach();

        Video::handleRelations($model, [
            'category_ids' => [$category->id],
            'genre_ids' => [$genre->id],
        ]);
        $model->refresh();
        $this->assertCount(1, $model->categories);
        $this->assertCount(1, $model->genres);
    }

    public function testSyncCategories()
    {
        /** @var Video $model */
        $ids = Category::factory()->count(3)->create()->pluck('id')->toArray();

        $model = Video::factory()->create();
        Video::handleRelations($model, ['category_ids' => [$ids[0]]]);
        $this->assertDatabaseHas('categories_videos', ['category_id' => $ids[0], 'video_id' => $model->id]);

        Video::handleRelations($model, ['category_ids' => [$ids[1], $ids[2]]]);
        $this->assertDatabaseMissing('categories_videos', ['category_id' => $ids[0], 'video_id' => $model->id]);
        $this->assertDatabaseHas('categories_videos', ['category_id' => $ids[1], 'video_id' => $model->id]);
        $this->assertDatabaseHas('categories_videos', ['category_id' => $ids[2], 'video_id' => $model->id]);
    }

    public function testSyncGenres()
    {
        /** @var Video $model */
        $ids = Genre::factory()->count(3)->create()->pluck('id')->toArray();

        $model = Video::factory()->create();
        Video::handleRelations($model, ['genre_ids' => [$ids[0]]]);
        $this->assertDatabaseHas('genres_videos', ['genre_id' => $ids[0], 'video_id' => $model->id]);

        Video::handleRelations($model, ['genre_ids' => [$ids[1], $ids[2]]]);
        $this->assertDatabaseMissing('genres_videos', ['genre_id' => $ids[0], 'video_id' => $model->id]);
        $this->assertDatabaseHas('genres_videos', ['genre_id' => $ids[1], 'video_id' => $model->id]);
        $this->assertDatabaseHas('genres_videos', ['genre_id' => $ids[2], 'video_id' => $model->id]);
    }


    private function assertHasCategory($videoId, $categoryId)
    {
        $this->assertDatabaseHas('categories_videos', [
            'video_id' => $videoId,
            'category_id' => $categoryId,
        ]);
    }

    private function assertHasGenre($videoId, $genreId)
    {
        $this->assertDatabaseHas('genres_videos', [
            'video_id' => $videoId,
            'genre_id' => $genreId,
        ]);
    }
}
