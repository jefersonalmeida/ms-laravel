<?php

namespace Tests\Feature\Models;

use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class CategoryTest extends TestCase
{
    use DatabaseMigrations;

    public function testList()
    {
        Category::factory()->count(1)->create();
        $collection = Category::all();
        $this->assertCount(1, $collection);
        $keys = array_keys($collection->first()->getAttributes());
        $this->assertEqualsCanonicalizing([
            'id',
            'is_active',
            'name',
            'description',
            'created_at',
            'updated_at',
            'deleted_at'
        ], $keys);
    }

    public function testCreate()
    {
        $model = Category::create(['name' => 'Test']);
        $model->refresh();

        $this->assertEquals('Test', $model->name);
        $this->assertNull($model->description);
        $this->assertTrue($model->is_active);

        $model = Category::create(['name' => 'Test', 'description' => null]);
        $this->assertNull($model->description);

        $model = Category::create(['name' => 'Test', 'description' => 'Description']);
        $this->assertEquals('Description', $model->description);

        $model = Category::create(['name' => 'Test', 'is_active' => false]);
        $this->assertFalse($model->is_active);
    }

    public function testUpdate()
    {
        /** @var Category $category */
        $category = Category::factory()->create()->first();

        $data = [
            'name' => 'test_name_updated',
            'description' => 'test_description_updated',
            'is_active' => false,
        ];
        $category->update($data);

        foreach ($data as $key => $item) {
            $this->assertEquals($item, $category->{$key});
        }
    }
}