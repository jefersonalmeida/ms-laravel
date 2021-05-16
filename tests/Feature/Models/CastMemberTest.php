<?php

namespace Tests\Feature\Models;

use App\Models\CastMember;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Str;
use Tests\TestCase;

class CastMemberTest extends TestCase
{
    use DatabaseMigrations;

    public function testList()
    {
        CastMember::factory()->count(1)->create();
        $collection = CastMember::all();
        $this->assertCount(1, $collection);
        $keys = array_keys($collection->first()->getAttributes());
        $this->assertEqualsCanonicalizing([
            'id',
            'name',
            'type',
            'created_at',
            'updated_at',
            'deleted_at'
        ], $keys);
    }

    public function testCreate()
    {
        $model = CastMember::create(['name' => 'Test', 'type' => CastMember::TYPE_ACTOR]);
        $model->refresh();

        $this->assertTrue(Str::isUuid($model->id));
        $this->assertEquals('Test', $model->name);
        $this->assertEquals(CastMember::TYPE_ACTOR, $model->type);
        $this->assertNull($model->deleted_at);
    }

    public function testUpdate()
    {
        /** @var CastMember $model */
        $model = CastMember::factory()->create(['type' => CastMember::TYPE_ACTOR]);

        $data = [
            'name' => 'test_name_director',
            'type' => CastMember::TYPE_DIRECTOR,
        ];
        $model->update($data);

        foreach ($data as $key => $item) {
            $this->assertEquals($item, $model->{$key});
        }
    }

    public function testDeleteAndRestore()
    {
        /** @var CastMember $model */
        $model = CastMember::factory()->create();
        $model->delete();
        $this->assertNull(CastMember::find($model->id));

        $model->restore();
        $this->assertNotNull(CastMember::find($model->id));
    }
}
