<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Response;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function testIndex()
    {
        /** @var Genre $model */
        $model = Genre::factory()->create();
        $response = $this->get(route('genres.index'));
        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([$model->toArray()]);
    }

    public function testShow()
    {
        /** @var Genre $model */
        $model = Genre::factory()->create();
        $response = $this->get(route('genres.show', ['genre' => $model->id]));
        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson($model->toArray());
    }

    public function testInvalidateData()
    {
        $response = $this->json('POST', route('genres.store'), []);
        $this->assertInvalidationRequire($response);

        $response = $this->json('POST', route('genres.store'), [
            'name' => str_repeat('a', 256),
            'is_active' => 'A'
        ]);
        $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);

        /** @var Genre $model */
        $model = Genre::factory()->create();

        $response = $this->json('PUT', route('genres.update', ['genre' => $model->id]), []);
        $this->assertInvalidationRequire($response);

        $response = $this->json('PUT', route('genres.update', ['genre' => $model->id]), [
            'name' => str_repeat('a', 256),
            'is_active' => 'A'
        ]);
        $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);
    }

    private function assertInvalidationRequire(TestResponse $response)
    {
        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonMissingValidationErrors(['is_active'])
            ->assertJsonFragment([
                __('validation.required', ['attribute' => 'name'])
            ]);
    }

    private function assertInvalidationMax(TestResponse $response)
    {
        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['name'])
            ->assertJsonFragment([
                __('validation.max.string', ['attribute' => 'name', 'max' => 255])
            ]);
    }

    private function assertInvalidationBoolean(TestResponse $response)
    {
        $response
            ->assertStatus(Response::HTTP_UNPROCESSABLE_ENTITY)
            ->assertJsonValidationErrors(['is_active'])
            ->assertJsonFragment([
                __('validation.boolean', ['attribute' => 'is active'])
            ]);
    }

    public function testStore()
    {
        $response = $this->json('POST', route('genres.store'), [
            'name' => 'test_name'
        ]);

        $id = $response->json('id');
        $model = Genre::find($id);

        $response
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson($model->toArray())
            ->assertJsonFragment([
                'is_active' => true,
                'name' => 'test_name',
            ]);

        $response = $this->json('POST', route('genres.store'), [
            'is_active' => false,
            'name' => 'test_name',
        ]);
        $response->assertJsonFragment([
            'is_active' => false,
        ]);
    }

    public function testUpdate()
    {
        /** @var Genre $model */
        $model = Genre::factory()->create([
            'is_active' => false,
        ]);

        $response = $this->json('PUT', route('genres.update', ['genre' => $model->id]), [
            'is_active' => true,
            'name' => 'test_name',
        ]);

        $id = $response->json('id');
        $model = Genre::find($id);

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson($model->toArray())
            ->assertJsonFragment([
                'is_active' => true,
                'name' => 'test_name',
            ]);
    }

    public function testDelete()
    {
        /** @var Genre $model */
        $model = Genre::factory()->create();

        $response = $this->json('DELETE', route('genres.destroy', ['genre' => $model->id]));
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertNull(Genre::find($model->id));
        $this->assertNotNull(Genre::withTrashed()->find($model->id));

        $response = $this->json('DELETE', route('genres.destroy', ['genre' => $model->id]));
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
