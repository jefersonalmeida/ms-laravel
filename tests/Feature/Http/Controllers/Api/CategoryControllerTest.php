<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Response;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations;

    public function testIndex()
    {
        /** @var Category $model */
        $model = Category::factory()->create();
        $response = $this->get(route('categories.index'));
        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([$model->toArray()]);
    }

    public function testShow()
    {
        /** @var Category $model */
        $model = Category::factory()->create();
        $response = $this->get(route('categories.show', ['category' => $model->id]));
        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson($model->toArray());
    }

    public function testInvalidateData()
    {
        $response = $this->json('POST', route('categories.store'), []);
        $this->assertInvalidationRequire($response);

        $response = $this->json('POST', route('categories.store'), [
            'name' => str_repeat('a', 256),
            'is_active' => 'A'
        ]);
        $this->assertInvalidationMax($response);
        $this->assertInvalidationBoolean($response);

        /** @var Category $model */
        $model = Category::factory()->create();

        $response = $this->json('PUT', route('categories.update', ['category' => $model->id]), []);
        $this->assertInvalidationRequire($response);

        $response = $this->json('PUT', route('categories.update', ['category' => $model->id]), [
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
        $response = $this->json('POST', route('categories.store'), [
            'name' => 'test_name'
        ]);

        $id = $response->json('id');
        $model = Category::find($id);

        $response
            ->assertStatus(Response::HTTP_CREATED)
            ->assertJson($model->toArray())
            ->assertJsonFragment([
                'is_active' => true,
                'name' => 'test_name',
                'description' => null,
            ]);

        $response = $this->json('POST', route('categories.store'), [
            'is_active' => false,
            'name' => 'test_name',
            'description' => 'test_description',
        ]);
        $response->assertJsonFragment([
            'is_active' => false,
            'description' => 'test_description',
        ]);
    }

    public function testUpdate()
    {
        /** @var Category $model */
        $model = Category::factory()->create([
            'is_active' => false,
            'description' => 'test_description',
        ]);

        $response = $this->json('PUT', route('categories.update', ['category' => $model->id]), [
            'is_active' => true,
            'name' => 'test_name',
            'description' => 'test_description_updated',
        ]);

        $id = $response->json('id');
        $model = Category::find($id);

        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson($model->toArray())
            ->assertJsonFragment([
                'is_active' => true,
                'name' => 'test_name',
                'description' => 'test_description_updated',
            ]);

        $response = $this->json('PUT', route('categories.update', ['category' => $model->id]), [
            'is_active' => true,
            'name' => 'test_name',
            'description' => '',
        ]);
        $response->assertJsonFragment([
            'description' => null,
        ]);

        $model->update(['description' => 'test_description_updated']);

        $response = $this->json('PUT', route('categories.update', ['category' => $model->id]), [
            'is_active' => true,
            'name' => 'test_name',
            'description' => null,
        ]);
        $response->assertJsonFragment([
            'description' => null,
        ]);
    }

    public function testDelete()
    {
        /** @var Category $model */
        $model = Category::factory()->create();

        $response = $this->json('DELETE', route('categories.destroy', ['category' => $model->id]));
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $response = $this->json('DELETE', route('categories.destroy', ['category' => $model->id]));
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }
}
