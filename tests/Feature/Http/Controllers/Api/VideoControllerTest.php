<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\VideoController;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Exception;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;
use Mockery;
use Tests\Exceptions\TestException;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class VideoControllerTest extends TestCase
{
    use DatabaseMigrations;
    use TestValidations;
    use TestSaves;

    private Video $model;
    private array $sendData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = Video::factory()->create();
        $this->sendData = [
            'title' => 'test_title',
            'description' => 'test_description',
            'year_launched' => 2020,
            'opened' => false,
            'rating' => Video::RATING_LIST[0],
            'duration' => 90,
        ];
    }

    public function testIndex()
    {
        $response = $this->get(route('videos.index'));
        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([$this->model->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('videos.show', ['video' => $this->model->id]));
        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson($this->model->toArray());
    }

    public function testInvalidationRequired()
    {
        $data = [
            'title' => '',
            'description' => '',
            'year_launched' => '',
            'rating' => '',
            'duration' => '',
        ];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');
    }

    public function testInvalidationMax()
    {
        $data = ['title' => str_repeat('a', 256)];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);
    }

    public function testInvalidationInteger()
    {
        $data = ['duration' => 'S'];
        $this->assertInvalidationInStoreAction($data, 'integer');
        $this->assertInvalidationInUpdateAction($data, 'integer');
    }

    public function testInvalidateBoolean()
    {
        $data = ['opened' => 'A'];
        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');
    }

    public function testInvalidateYear()
    {
        $data = ['year_launched' => 'A'];
        $this->assertInvalidationInStoreAction($data, 'date_format', ['format' => 'Y']);
        $this->assertInvalidationInUpdateAction($data, 'date_format', ['format' => 'Y']);
    }

    public function testInvalidateIn()
    {
        $data = ['rating' => 0];
        $this->assertInvalidationInStoreAction($data, 'in');
        $this->assertInvalidationInUpdateAction($data, 'in');
    }

    public function testInvalidateCategoryIds()
    {
        $data = ['category_ids' => 'a'];
        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');

        $data = ['category_ids' => [100]];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');

        $category = Category::factory()->create();
        $category->delete();
        $data = ['category_ids' => [$category->id]];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
    }

    public function testInvalidateGenreIds()
    {
        $data = ['genre_ids' => 'a'];
        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');

        $data = ['genre_ids' => [100]];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');

        $genre = Genre::factory()->create();
        $genre->delete();
        $data = ['genre_ids' => [$genre->id]];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
    }

    public function testRollbackStore()
    {
        $controller = Mockery::mock(VideoController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn($this->sendData);

        $controller->shouldReceive('rulesStore')
            ->withAnyArgs()
            ->andReturn([]);

        $controller
            ->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new TestException());

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('get')
            ->withAnyArgs()
            ->andReturnNull();

        $hasError = false;
        try {
            $controller->store($request);
        } catch (TestException $e) {
            $this->assertCount(1, Video::all());
            $hasError = true;
        }
        $this->assertTrue($hasError);
    }

    public function testRollbackUpdate()
    {
        $controller = Mockery::mock(VideoController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller->shouldReceive('findOrFail')
            ->withAnyArgs()
            ->andReturn($this->model);

        $controller->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn($this->sendData);

        $controller->shouldReceive('rulesUpdate')
            ->withAnyArgs()
            ->andReturn([]);

        $controller
            ->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new TestException());

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('get')
            ->withAnyArgs()
            ->andReturnNull();

        $hasError = false;
        try {
            $controller->update($request, 1);
        } catch (TestException $e) {
            $this->assertCount(1, Video::all());
            $hasError = true;
        }
        $this->assertTrue($hasError);
    }

    /**
     * @throws Exception
     */
    public function testSave()
    {
        /** @var Category $category */
        $category = Category::factory()->create();
        /** @var Genre $genre */
        $genre = Genre::factory()->create();
        $genre->categories()->sync([$category->id]);

        $data = [
            [
                'send_data' => $this->sendData + ['category_ids' => [$category->id], 'genre_ids' => [$genre->id]],
                'test_data' => $this->sendData + ['opened' => false, 'deleted_at' => null],
            ],
            [
                'send_data' => $this->sendData + [
                        'opened' => true,
                        'category_ids' => [$category->id],
                        'genre_ids' => [$genre->id],
                    ],
                'test_data' => $this->sendData + ['opened' => true, 'deleted_at' => null],
            ],
            [
                'send_data' => $this->sendData + [
                        'rating' => Video::RATING_LIST[1],
                        'category_ids' => [$category->id],
                        'genre_ids' => [$genre->id],
                    ],
                'test_data' => $this->sendData + ['rating' => Video::RATING_LIST[1], 'deleted_at' => null],
            ]
        ];

        foreach ($data as $key => $value) {
            $response = $this->assertStore($value['send_data'], $value['test_data']);
            $response->assertJsonStructure(['created_at', 'updated_at']);
            $this->assertHasCategory($response->json('id'), $value['send_data']['category_ids'][0]);
            $this->assertHasGenre($response->json('id'), $value['send_data']['genre_ids'][0]);

            $response = $this->assertUpdate($value['send_data'], $value['test_data']);
            $response->assertJsonStructure(['created_at', 'updated_at']);
            $this->assertHasCategory($response->json('id'), $value['send_data']['category_ids'][0]);
            $this->assertHasGenre($response->json('id'), $value['send_data']['genre_ids'][0]);
        }
    }

    public function testDelete()
    {
        $response = $this->json('DELETE', route('videos.destroy', ['video' => $this->model->id]));
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertNull(Video::find($this->model->id));
        $this->assertNotNull(Video::withTrashed()->find($this->model->id));

        $response = $this->json('DELETE', route('videos.destroy', ['video' => $this->model->id]));
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testSyncCategories()
    {
        /** @var Genre $genre */
        $categoryIds = Category::factory()->count(3)->create()->pluck('id')->toArray();
        $genre = Genre::factory()->create();
        $genre->categories()->sync($categoryIds);

        $response = $this->json('POST', $this->routeStore(),
            $this->sendData + [
                'genre_ids' => [$genre->id],
                'category_ids' => [$categoryIds[0]],
            ]
        );
        $this->assertDatabaseHas('categories_videos', [
            'category_id' => $categoryIds[0],
            'video_id' => $response->json('id'),
        ]);

        $response = $this->json('PUT',
            route('videos.update', ['video' => $response->json('id')]),
            $this->sendData + [
                'genre_ids' => [$genre->id],
                'category_ids' => [$categoryIds[1], $categoryIds[2]],
            ]
        );

        $this->assertDatabaseMissing('categories_videos', [
            'category_id' => $categoryIds[0],
            'video_id' => $response->json('id'),
        ]);

        $this->assertDatabaseHas('categories_videos', [
            'category_id' => $categoryIds[1],
            'video_id' => $response->json('id'),
        ]);

        $this->assertDatabaseHas('categories_videos', [
            'category_id' => $categoryIds[2],
            'video_id' => $response->json('id'),
        ]);
    }

    public function testSyncGenres()
    {
        /** @var Genre[]|Collection $genres */
        $genres = Genre::factory()->count(3)->create();
        $genresId = $genres->pluck('id')->toArray();
        $categoryId = Category::factory()->create()->id;
        $genres->each(function ($genre) use ($categoryId) {
            $genre->categories()->sync([$categoryId]);
        });

        $response = $this->json('POST', $this->routeStore(),
            $this->sendData + [
                'category_ids' => [$categoryId],
                'genre_ids' => [$genresId[0]],
            ]
        );
        $this->assertDatabaseHas('genres_videos', [
            'genre_id' => $genresId[0],
            'video_id' => $response->json('id'),
        ]);

        $response = $this->json('PUT',
            route('videos.update', ['video' => $response->json('id')]),
            $this->sendData + [
                'genre_ids' => [$genresId[1], $genresId[2]],
                'category_ids' => [$categoryId],
            ]
        );

        $this->assertDatabaseMissing('genres_videos', [
            'genre_id' => $genresId[0],
            'video_id' => $response->json('id'),
        ]);

        $this->assertDatabaseHas('genres_videos', [
            'genre_id' => $genresId[1],
            'video_id' => $response->json('id'),
        ]);

        $this->assertDatabaseHas('genres_videos', [
            'genre_id' => $genresId[2],
            'video_id' => $response->json('id'),
        ]);
    }

    protected function routeStore(): string
    {
        return route('videos.store');
    }

    protected function routeUpdate(): string
    {
        return route('videos.update', ['video' => $this->model->id]);
    }

    protected function model(): string
    {
        return Video::class;
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
