<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Video;
use Exception;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Response;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class VideoControllerTest extends TestCase
{
    use DatabaseMigrations;
    use TestValidations;
    use TestSaves;

    private Video $model;
    private $sendData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = Video::factory()->create();
        $this->sendData = [
            'title' => 'test_title',
            'description' => 'test_description',
            'year_launched' => 2020,
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

    /**
     * @throws Exception
     */
    public function testSave()
    {
        $data = [
            [
                'send_data' => $this->sendData,
                'test_data' => $this->sendData + ['opened' => false, 'deleted_at' => null],
            ],
            [
                'send_data' => $this->sendData + ['opened' => true],
                'test_data' => $this->sendData + ['opened' => true, 'deleted_at' => null],
            ],
            [
                'send_data' => $this->sendData + ['rating' => Video::RATING_LIST[1]],
                'test_data' => $this->sendData + ['rating' => Video::RATING_LIST[1], 'deleted_at' => null],
            ]
        ];

        foreach ($data as $key => $value) {
            $response = $this->assertStore($value['send_data'], $value['test_data']);
            $response->assertJsonStructure(['created_at', 'updated_at']);

            $response = $this->assertUpdate($value['send_data'], $value['test_data']);
            $response->assertJsonStructure(['created_at', 'updated_at']);
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
}
