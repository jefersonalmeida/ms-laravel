<?php

namespace Tests\Unit\Models;

use App\Models\Traits\UploadFiles;
use App\Models\Traits\Uuid;
use App\Models\Video;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\TestCase;

class VideoUnitTest extends TestCase
{
    private Video $model;

    private array $fillable = [
        'title',
        'description',
        'year_launched',
        'opened',
        'rating',
        'duration',
        'thumb_file',
        'video_file',
    ];

    private array $dates = [
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    private array $casts = [
        'id' => 'string',
        'opened' => 'boolean',
        'year_launched' => 'integer',
        'duration' => 'integer',
        'deleted_at' => 'datetime',
    ];

    private array $traits = [
        HasFactory::class,
        SoftDeletes::class,
        Uuid::class,
        UploadFiles::class,
    ];

    public function testFillableAttribute()
    {
        $this->assertEquals($this->fillable, $this->model->getFillable());
    }

    public function testIfUseTraits()
    {
        $this->assertEquals($this->traits, array_keys(class_uses(Video::class)));
    }

    public function testCastsAttribute()
    {
        $modelArr = $this->model->getCasts();
        $this->assertEqualsCanonicalizing($this->casts, $modelArr);
        $this->assertCount(count($this->casts), $modelArr);
    }

    public function testDatesAttribute()
    {
        $modelArr = $this->model->getDates();
        $this->assertEqualsCanonicalizing($this->dates, $modelArr);
        $this->assertCount(count($this->dates), $modelArr);
    }

    public function testIncrementing()
    {
        $this->assertFalse($this->model->incrementing);
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new Video();
    }
}
