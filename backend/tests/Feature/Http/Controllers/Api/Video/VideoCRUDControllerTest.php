<?php

namespace Tests\Feature\Http\Controllers\Api\Video;

use App\Http\Resources\VideoResource;
use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Exception;
use Illuminate\Http\Response;
use Illuminate\Support\Arr;
use Illuminate\Testing\TestResponse;
use Tests\Traits\TestResource;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class VideoCRUDControllerTest extends BaseVideoControllerTestCase
{
    use TestValidations;
    use TestSaves;
    use TestResource;

    private array $serializedFields = [
        'id',
        'title',
        'description',
        'year_launched',
        'rating',
        'duration',
        'rating',
        'opened',
        'thumb_file',
        'banner_file',
        'trailer_file',
        'video_file',
        'created_at',
        'updated_at',
        'deleted_at',
        'thumb_file_url',
        'banner_file_url',
        'trailer_file_url',
        'video_file_url',
        'categories' => [
            '*' => [
                'id',
                'name',
                'description',
                'is_active',
                'created_at',
                'updated_at',
                'deleted_at'
            ]
        ],
        'genres' => [
            '*' => [
                'id',
                'name',
                'is_active',
                'created_at',
                'updated_at',
                'deleted_at',
            ]
        ],
    ];

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
        $data = ['categories_id' => 'a'];
        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');

        $data = ['categories_id' => [100]];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');

        /** @var Category $category */
        $category = Category::factory()->create();
        $category->delete();
        $data = ['categories_id' => [$category->id]];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
    }

    public function testInvalidateGenreIds()
    {
        $data = ['genres_id' => 'a'];
        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');

        $data = ['genres_id' => [100]];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');

        /** @var Genre $genre */
        $genre = Genre::factory()->create();
        $genre->delete();
        $data = ['genres_id' => [$genre->id]];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
    }

    public function testIndex()
    {
        $response = $this->get(route('videos.index'));
        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'meta' => ['per_page' => 15]
            ])
            ->assertJsonStructure([
                'data' => ['*' => $this->serializedFields],
                'links' => [],
                'meta' => [],
            ]);

        $resource = VideoResource::collection(collect()->add($this->model));
        $this->assertResource($response, $resource);
        $this->assertIfFilesUrlExists($this->model, $response);
    }

    public function testShow()
    {
        $response = $this->get(route('videos.show', ['video' => $this->model->id]));
        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['data' => $this->serializedFields]);

        $this->assertResourceModel($response);
        $this->assertIfFilesUrlExists($this->model, $response);
    }

    /**
     * @throws Exception
     */
    public function testSaveWithoutFiles()
    {
        $testData = Arr::except($this->sendData, ['categories_id', 'genres_id']);
        $data = [
            [
                'send_data' => $this->sendData,
                'test_data' => $testData + ['opened' => false, 'deleted_at' => null],
            ],
            [
                'send_data' => $this->sendData + ['opened' => true],
                'test_data' => $testData + ['opened' => true, 'deleted_at' => null],
            ],
            [
                'send_data' => $this->sendData + ['rating' => Video::RATING_LIST[1]],
                'test_data' => $testData + ['rating' => Video::RATING_LIST[1], 'deleted_at' => null],
            ]
        ];

        foreach ($data as $value) {
            $response = $this->assertStore($value['send_data'], $value['test_data']);

            $response->assertJsonStructure(['data' => $this->serializedFields]);
            $this->assertResourceModel($response);

            $this->assertHasCategory($this->getIdFromResponse($response), $value['send_data']['categories_id'][0]);
            $this->assertHasGenre($this->getIdFromResponse($response), $value['send_data']['genres_id'][0]);

            $response = $this->assertUpdate($value['send_data'], $value['test_data']);

            $response->assertJsonStructure(['data' => $this->serializedFields]);
            $this->assertResourceModel($response);

            $this->assertHasCategory($this->getIdFromResponse($response), $value['send_data']['categories_id'][0]);
            $this->assertHasGenre($this->getIdFromResponse($response), $value['send_data']['genres_id'][0]);
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

    protected function assertResourceModel(TestResponse $response): void
    {
        $resource = new VideoResource($this->model()::find($this->getIdFromResponse($response)));
        $this->assertResource($response, $resource);
    }
}
