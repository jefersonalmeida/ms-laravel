<?php

namespace Tests\Feature\Models;

use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Tests\TestCase;

class GenreTest extends TestCase
{
    use DatabaseMigrations;

    public function testList()
    {
        Genre::factory()->count(1)->create();
        $collection = Genre::all();
        $this->assertCount(1, $collection);
        $keys = array_keys($collection->first()->getAttributes());
        $this->assertEqualsCanonicalizing([
            'id',
            'is_active',
            'name',
            'created_at',
            'updated_at',
            'deleted_at'
        ], $keys);
    }

    public function testCreate()
    {
        $model = Genre::create(['name' => 'Test']);
        $model->refresh();

        $this->assertTrue(Str::isUuid($model->id));
        $this->assertEquals('Test', $model->name);
        $this->assertTrue($model->is_active);

        $model = Genre::create(['name' => 'Test', 'is_active' => false]);
        $this->assertFalse($model->is_active);

        $model = Genre::create(['name' => 'Test', 'is_active' => true]);
        $this->assertTrue($model->is_active);
    }

    public function testUpdate()
    {
        /** @var Genre $model */
        $model = Genre::factory()->create();

        $data = [
            'name' => 'test_name_updated',
            'is_active' => false,
        ];
        $model->update($data);

        foreach ($data as $key => $item) {
            $this->assertEquals($item, $model->{$key});
        }
    }

    public function testDeleteAndRestore()
    {
        /** @var Genre $model */
        $model = Genre::factory()->create();
        $model->delete();
        $this->assertNull(Genre::find($model->id));

        $model->restore();
        $this->assertNotNull(Genre::find($model->id));
    }
}
