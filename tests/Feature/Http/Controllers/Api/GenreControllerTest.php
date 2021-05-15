<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Genre;
use Exception;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Response;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations;
    use TestValidations;
    use TestSaves;

    private Genre $model;

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
        $response = $this->assertStore($data, $data + ['is_active' => true, 'deleted_at' => null]);
        $response->assertJsonStructure(['created_at', 'updated_at']);

        $data = ['is_active' => false, 'name' => 'test_name'];
        $this->assertStore($data, $data + ['deleted_at' => null]);
    }

    /**
     * @throws Exception
     */
    public function testUpdate()
    {
        /** @var Genre $model */
        $this->model = Genre::factory()->create(['is_active' => false]);

        $data = ['is_active' => true, 'name' => 'test_name'];
        $response = $this->assertUpdate($data, $data + ['deleted_at' => null]);
        $response->assertJsonStructure(['created_at', 'updated_at']);

        $data['is_active'] = false;
        $this->assertUpdate($data, $data);
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

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = Genre::factory()->create();
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
}
