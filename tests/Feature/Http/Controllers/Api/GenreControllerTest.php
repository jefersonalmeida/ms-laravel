<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\GenreController;
use App\Models\Category;
use App\Models\Genre;
use Exception;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Mockery;
use Tests\Exceptions\TestException;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations;
    use TestValidations;
    use TestSaves;

    private Genre $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = Genre::factory()->create();
    }

    public function testIndex()
    {
        $response = $this->get(route('genres.index'));
        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([$this->model->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('genres.show', ['genre' => $this->model->id]));
        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson($this->model->toArray());
    }

    public function testInvalidateData()
    {
        $data = ['name' => '', 'category_ids' => ''];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');

        $data = ['name' => str_repeat('a', 256)];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);

        $data = ['is_active' => 'A'];
        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');

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
    public function testStore()
    {
        $category = Category::factory()->create();
        $data = ['name' => 'test_name'];
        $response = $this->assertStore(
            $data + ['category_ids' => [$category->id]],
            $data + ['is_active' => true, 'deleted_at' => null]
        );
        $response->assertJsonStructure(['created_at', 'updated_at']);

        $this->assertHasCategory($response->json('id'), $category->id);

        $data = ['is_active' => false, 'name' => 'test_name'];
        $this->assertStore(
            $data + ['category_ids' => [$category->id]],
            $data + ['deleted_at' => null]
        );
    }

    /**
     * @throws Exception
     */
    public function testUpdate()
    {
        $category = Category::factory()->create();

        $data = ['is_active' => true, 'name' => 'test_name'];
        $response = $this->assertUpdate(
            $data + ['category_ids' => [$category->id]],
            $data + ['is_active' => true, 'deleted_at' => null]);
        $response->assertJsonStructure(['created_at', 'updated_at']);

        $data['is_active'] = false;
        $this->assertUpdate($data + ['category_ids' => [$category->id]], $data);
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
            'category_ids' => [$categoryIds[0]]
        ];

        $response = $this->json('POST', $this->routeStore(), $sendData);
        $this->assertDatabaseHas('categories_genres', [
            'category_id' => $categoryIds[0],
            'genre_id' => $response->json('id'),
        ]);

        $sendData = [
            'name' => 'Test',
            'category_ids' => [$categoryIds[1], $categoryIds[2]]
        ];

        $response = $this->json('PUT',
            route('genres.update', ['genre' => $response->json('id')]),
            $sendData
        );

        $this->assertDatabaseMissing('categories_genres', [
            'category_id' => $categoryIds[0],
            'genre_id' => $response->json('id'),
        ]);

        $this->assertDatabaseHas('categories_genres', [
            'category_id' => $categoryIds[1],
            'genre_id' => $response->json('id'),
        ]);

        $this->assertDatabaseHas('categories_genres', [
            'category_id' => $categoryIds[2],
            'genre_id' => $response->json('id'),
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

    private function assertHasCategory($genreId, $categoryId)
    {
        $this->assertDatabaseHas('categories_genres', [
            'genre_id' => $genreId,
            'category_id' => $categoryId,
        ]);
    }
}
