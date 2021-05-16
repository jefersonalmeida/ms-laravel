<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\BasicCRUDController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\ValidationException;
use Mockery;
use ReflectionClass;
use ReflectionException;
use Tests\Stubs\Controllers\CategoryControllerStub;
use Tests\Stubs\Models\CategoryStub;
use Tests\TestCase;

class BasicCRUDControllerTest extends TestCase
{
    private CategoryControllerStub $controller;

    protected function setUp(): void
    {
        parent::setUp();
        CategoryStub::dropTable();
        CategoryStub::createTable();
        $this->controller = new CategoryControllerStub();
    }

    protected function tearDown(): void
    {
        CategoryStub::dropTable();
        parent::tearDown();
    }

    public function testIndex()
    {
        $model = CategoryStub::create(['name' => 'test_name', 'description' => 'test_description']);
        $this->assertEquals([$model->toArray()], $this->controller->index()->toArray());
    }

    public function testInvalidationDataInStore()
    {
        $this->expectException(ValidationException::class);

        $request = Mockery::mock(Request::class);
        $request
            ->shouldReceive('all')
            ->once()
            ->andReturn(['name' => '']);

        $this->controller->store($request);
    }

    /**
     * @throws ValidationException
     */
    public function testStore()
    {
        $request = Mockery::mock(Request::class);
        $request
            ->shouldReceive('all')
            ->once()
            ->andReturn(['name' => 'test_name', 'description' => 'test_description']);

        $model = $this->controller->store($request);
        $this->assertEquals($model->toArray(), CategoryStub::find(1)->toArray());
    }

    /**
     * @throws ReflectionException
     */
    public function testIfFindOrFailFetchModel()
    {
        $model = CategoryStub::create(['name' => 'test_name', 'description' => 'test_description']);

        $reflectionClass = new ReflectionClass(BasicCRUDController::class);
        $reflectionMethod = $reflectionClass->getMethod('findOrFail');
        $reflectionMethod->setAccessible(true);
        $result = $reflectionMethod->invokeArgs($this->controller, [$model->id]);

        $this->assertInstanceOf(CategoryStub::class, $result);
    }

    /**
     * @throws ReflectionException
     */
    public function testIfFindOrFailThrowExceptionWhenIdInvalid()
    {
        $this->expectException(ModelNotFoundException::class);

        $reflectionClass = new ReflectionClass(BasicCRUDController::class);
        $reflectionMethod = $reflectionClass->getMethod('findOrFail');
        $reflectionMethod->setAccessible(true);
        $result = $reflectionMethod->invokeArgs($this->controller, [0]);
        $this->assertInstanceOf(CategoryStub::class, $result);
    }

    public function testShow()
    {
        $model = CategoryStub::create(['name' => 'test_name', 'description' => 'test_description']);
        $result = $this->controller->show($model->id);
        $this->assertEquals($result->toArray(), CategoryStub::find(1)->toArray());
    }

    /**
     * @throws ValidationException
     */
    public function testUpdate()
    {
        $model = CategoryStub::create(['name' => 'test_name', 'description' => 'test_description']);

        $request = Mockery::mock(Request::class);
        $request->shouldReceive('all')
            ->once()
            ->andReturn(['name' => 'test_name_updated', 'description' => 'test_description_updated']);

        $model = $this->controller->update($request, $model->id);
        $this->assertEquals($model->toArray(), CategoryStub::find(1)->toArray());
    }

    public function testDestroy()
    {
        $model = CategoryStub::create(['name' => 'test_name', 'description' => 'test_description']);
        $response = $this->controller->destroy($model->id);

        $this->createTestResponse($response)->assertStatus(Response::HTTP_NO_CONTENT);
        $this->assertCount(0, CategoryStub::all());
    }
}
