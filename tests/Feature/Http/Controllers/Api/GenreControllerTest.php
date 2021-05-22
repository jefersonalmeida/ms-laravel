<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\GenreController;
use App\Http\Resources\GenreResource;
use App\Models\Category;
use App\Models\Genre;
use Exception;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Testing\TestResponse;
use Mockery;
use Tests\Exceptions\TestException;
use Tests\TestCase;
use Tests\Traits\TestResource;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations;
    use TestValidations;
    use TestSaves;
    use TestResource;

    private Genre $model;
    private array $serializedFields = [
        'id',
        'is_active',
        'name',
        'created_at',
        'updated_at',
        'deleted_at',
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
        ]
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = Genre::factory()->create();
    }

    public function testIndex()
    {
        $response = $this->get(route('genres.index'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'meta' => ['per_page' => 15]
            ])
            ->assertJsonStructure([
                'data' => ['*' => $this->serializedFields],
                'links' => [],
                'meta' => [],
            ]);

        $resource = GenreResource::collection(collect()->add($this->model));
        $this->assertResource($response, $resource);
    }

    public function testShow()
    {
        $response = $this->get(route('genres.show', ['genre' => $this->model->id]));
        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['data' => $this->serializedFields]);

        $this->assertResourceModel($response);
    }

    public function testInvalidateData()
    {
        $data = ['name' => '', 'categories_id' => ''];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');

        $data = ['name' => str_repeat('a', 256)];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);

        $data = ['is_active' => 'A'];
        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');

        $data = ['categories_id' => 'a'];
        $this->assertInvalidationInStoreAction($data, 'array');
        $this->assertInvalidationInUpdateAction($data, 'array');

        $data = ['categories_id' => [100]];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');

        $category = Category::factory()->create();
        $category->delete();

        $data = ['categories_id' => [$category->id]];
        $this->assertInvalidationInStoreAction($data, 'exists');
        $this->assertInvalidationInUpdateAction($data, 'exists');
    }

    public function testRollbackStore()
    {
        $controller = Mockery::mock(GenreController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn([
                'name' => 'test'
            ]);

        $controller->shouldReceive('rulesStore')
            ->withAnyArgs()
            ->andReturn([]);

        $controller
            ->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new TestException());

        $request = Mockery::mock(Request::class);

        $hasError = false;
        try {
            $controller->store($request);
        } catch (TestException $e) {
            $this->assertCount(1, Genre::all());
            $hasError = true;
        }
        $this->assertTrue($hasError);
    }

    public function testRollbackUpdate()
    {
        $controller = Mockery::mock(GenreController::class)
            ->makePartial()
            ->shouldAllowMockingProtectedMethods();

        $controller->shouldReceive('findOrFail')
            ->withAnyArgs()
            ->andReturn($this->model);

        $controller->shouldReceive('validate')
            ->withAnyArgs()
            ->andReturn([
                'name' => 'test'
            ]);

        $controller->shouldReceive('rulesStore')
            ->withAnyArgs()
            ->andReturn([]);

        $controller
            ->shouldReceive('handleRelations')
            ->once()
            ->andThrow(new TestException());

        $request = Mockery::mock(Request::class);

        $hasError = false;
        try {
            $controller->update($request, 1);
        } catch (TestException $e) {
            $this->assertCount(1, Genre::all());
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

        $data = [
            [
                'send_data' => ['name' => 'test', 'categories_id' => [$category->id]],
                'test_data' => ['name' => 'test', 'is_active' => true]
            ],
            [
                'send_data' => ['name' => 'test', 'is_active' => false, 'categories_id' => [$category->id]],
                'test_data' => ['name' => 'test', 'is_active' => false]
            ]
        ];

        foreach ($data as $test) {
            $response = $this->assertStore($test['send_data'], $test['test_data']);
            $response->assertJsonStructure(['data' => $this->serializedFields]);
            $this->assertResourceModel($response);

            $response = $this->assertUpdate($test['send_data'], $test['test_data']);
            $response->assertJsonStructure(['data' => $this->serializedFields]);
            $this->assertResourceModel($response);
        }
    }

    public function testDelete()
    {
        $response = $this->json('DELETE', route('genres.destroy', ['genre' => $this->model->id]));
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertNull(Genre::find($this->model->id));
        $this->assertNotNull(Genre::withTrashed()->find($this->model->id));

        $response = $this->json('DELETE', route('genres.destroy', ['genre' => $this->model->id]));
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    public function testSyncCategories()
    {
        $categoryIds = Category::factory()->count(3)->create()->pluck('id')->toArray();

        $sendData = [
            'name' => 'Test',
            'categories_id' => [$categoryIds[0]]
        ];

        $response = $this->json('POST', $this->routeStore(), $sendData);
        $this->assertDatabaseHas('categories_genres', [
            'category_id' => $categoryIds[0],
            'genre_id' => $this->getIdFromResponse($response),
        ]);

        $sendData = [
            'name' => 'Test',
            'categories_id' => [$categoryIds[1], $categoryIds[2]]
        ];

        $response = $this->json('PUT',
            route('genres.update', ['genre' => $this->getIdFromResponse($response)]),
            $sendData
        );

        $this->assertDatabaseMissing('categories_genres', [
            'category_id' => $categoryIds[0],
            'genre_id' => $this->getIdFromResponse($response),
        ]);

        $this->assertDatabaseHas('categories_genres', [
            'category_id' => $categoryIds[1],
            'genre_id' => $this->getIdFromResponse($response),
        ]);

        $this->assertDatabaseHas('categories_genres', [
            'category_id' => $categoryIds[2],
            'genre_id' => $this->getIdFromResponse($response),
        ]);
    }

    protected function routeStore(): string
    {
        return route('genres.store');
    }

    protected function routeUpdate(): string
    {
        return route('genres.update', ['genre' => $this->model->id]);
    }

    protected function model(): string
    {
        return Genre::class;
    }

    protected function assertResourceModel(TestResponse $response): void
    {
        $resource = new GenreResource($this->model()::find($this->getIdFromResponse($response)));
        $this->assertResource($response, $resource);
    }
}
