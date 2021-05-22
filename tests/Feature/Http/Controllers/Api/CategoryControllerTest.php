<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Exception;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Response;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use Tests\Traits\TestResource;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations;
    use TestValidations;
    use TestSaves;
    use TestResource;

    private Category $model;
    private array $serializedFields = [
        'id',
        'is_active',
        'name',
        'description',
        'created_at',
        'updated_at',
        'deleted_at',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = Category::factory()->create();
    }

    public function testIndex()
    {
        $response = $this->get(route('categories.index'));

        $response->assertStatus(Response::HTTP_OK)
            ->assertJson([
                'meta' => ['per_page' => 15]
            ])
            ->assertJsonStructure([
                'data' => ['*' => $this->serializedFields],
                'links' => [],
                'meta' => [],
            ]);

        $resource = CategoryResource::collection(collect()->add($this->model));
        $this->assertResource($response, $resource);
    }

    public function testShow()
    {
        $response = $this->get(route('categories.show', ['category' => $this->model->id]));
        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJsonStructure(['data' => $this->serializedFields]);

        $this->assertResourceModel($response);
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
        $response = $this->assertStore(
            $data,
            $data + ['description' => null, 'is_active' => true, 'deleted_at' => null]
        );
        $response->assertJsonStructure(['data' => $this->serializedFields]);

        $data = ['is_active' => false, 'name' => 'test_name', 'description' => 'test_description'];
        $this->assertStore($data, $data + ['deleted_at' => null]);

        $this->assertResourceModel($response);
    }

    /**
     * @throws Exception
     */
    public function testUpdate()
    {
        $data = [
            'is_active' => true,
            'name' => 'test_name',
            'description' => 'test_description_updated',
        ];
        $response = $this->assertUpdate($data, $data + ['deleted_at' => null]);
        $response->assertJsonStructure(['data' => $this->serializedFields]);
        $this->assertResourceModel($response);

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

    protected function assertResourceModel(TestResponse $response): void
    {
        $resource = new CategoryResource($this->model()::find($this->getIdFromResponse($response)));
        $this->assertResource($response, $resource);
    }
}
