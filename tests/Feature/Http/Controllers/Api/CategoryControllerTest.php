<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Category;
use Exception;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Response;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations;
    use TestValidations;
    use TestSaves;

    private Category $model;

    public function testIndex()
    {
        $response = $this->get(route('categories.index'));
        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([$this->model->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('categories.show', ['category' => $this->model->id]));
        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson($this->model->toArray());
    }

    public function testInvalidateData()
    {
        $data = ['name' => ''];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');

        $data = ['name' => str_repeat('a', 256)];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);


        $data = ['is_active' => 'A'];
        $this->assertInvalidationInStoreAction($data, 'boolean');
        $this->assertInvalidationInUpdateAction($data, 'boolean');
    }

    /**
     * @throws Exception
     */
    public function testStore()
    {
        $data = ['name' => 'test_name'];
        $response = $this->assertStore($data,
            $data + ['description' => null, 'is_active' => true, 'deleted_at' => null]
        );
        $response->assertJsonStructure(['created_at', 'updated_at']);

        $data = ['is_active' => false, 'name' => 'test_name', 'description' => 'test_description'];
        $this->assertStore($data, $data + ['deleted_at' => null]);
    }

    /**
     * @throws Exception
     */
    public function testUpdate()
    {
        /** @var Category $model */
        $this->model = Category::factory()->create([
            'is_active' => false,
            'description' => 'test_description',
        ]);

        $data = [
            'is_active' => true,
            'name' => 'test_name',
            'description' => 'test_description_updated',
        ];
        $response = $this->assertUpdate($data, $data + ['deleted_at' => null]);
        $response->assertJsonStructure(['created_at', 'updated_at']);

        $data['description'] = '';
        $this->assertUpdate($data, array_merge($data, ['description' => null]));

        $data['description'] = 'test_description_updated';
        $this->assertUpdate($data, array_merge($data, ['description' => 'test_description_updated']));

        $data['description'] = null;
        $this->assertUpdate($data, array_merge($data, ['description' => null]));
    }

    public function testDelete()
    {
        $response = $this->json('DELETE', route('categories.destroy', ['category' => $this->model->id]));
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertNull(Category::find($this->model->id));
        $this->assertNotNull(Category::withTrashed()->find($this->model->id));

        $response = $this->json('DELETE', route('categories.destroy', ['category' => $this->model->id]));
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = Category::factory()->create();
    }

    protected function routeStore(): string
    {
        return route('categories.store');
    }

    protected function routeUpdate(): string
    {
        return route('categories.update', ['category' => $this->model->id]);
    }

    protected function model(): string
    {
        return Category::class;
    }
}
