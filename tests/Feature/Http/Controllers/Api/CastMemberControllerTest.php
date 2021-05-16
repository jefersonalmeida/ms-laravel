<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\CastMember;
use Exception;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Response;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CastMemberControllerTest extends TestCase
{
    use DatabaseMigrations;
    use TestValidations;
    use TestSaves;

    private CastMember $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = CastMember::factory()->create(['type' => CastMember::TYPE_DIRECTOR]);
    }

    public function testIndex()
    {
        $response = $this->get(route('cast-members.index'));
        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson([$this->model->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('cast-members.show', ['cast_member' => $this->model->id]));
        $response
            ->assertStatus(Response::HTTP_OK)
            ->assertJson($this->model->toArray());
    }

    public function testInvalidateData()
    {
        $data = ['name' => '', 'type' => ''];
        $this->assertInvalidationInStoreAction($data, 'required');
        $this->assertInvalidationInUpdateAction($data, 'required');

        $data = ['name' => str_repeat('a', 256)];
        $this->assertInvalidationInStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationInUpdateAction($data, 'max.string', ['max' => 255]);

        $data = ['type' => 'A'];
        $this->assertInvalidationInStoreAction($data, 'in');
        $this->assertInvalidationInUpdateAction($data, 'in');
    }

    /**
     * @throws Exception
     */
    public function testStore()
    {
        $data = [
            ['name' => 'test_name_actor', 'type' => CastMember::TYPE_ACTOR],
            ['name' => 'test_name_director', 'type' => CastMember::TYPE_DIRECTOR],
        ];

        foreach ($data as $value) {
            $response = $this->assertStore($value, $value + ['deleted_at' => null]);
            $response->assertJsonStructure(['created_at', 'updated_at']);
        }
    }

    /**
     * @throws Exception
     */
    public function testUpdate()
    {
        $data = ['name' => 'test_name_actor', 'type' => CastMember::TYPE_ACTOR];
        $response = $this->assertUpdate($data, $data + ['deleted_at' => null]);
        $response->assertJsonStructure(['created_at', 'updated_at']);
    }

    public function testDelete()
    {
        $response = $this->json('DELETE', route('cast-members.destroy', ['cast_member' => $this->model->id]));
        $response->assertStatus(Response::HTTP_NO_CONTENT);

        $this->assertNull(CastMember::find($this->model->id));
        $this->assertNotNull(CastMember::withTrashed()->find($this->model->id));

        $response = $this->json('DELETE', route('cast-members.destroy', ['cast_member' => $this->model->id]));
        $response->assertStatus(Response::HTTP_NOT_FOUND);
    }

    protected function routeStore(): string
    {
        return route('cast-members.store');
    }

    protected function routeUpdate(): string
    {
        return route('cast-members.update', ['cast_member' => $this->model->id]);
    }

    protected function model(): string
    {
        return CastMember::class;
    }
}
