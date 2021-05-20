<?php

namespace Tests\Unit\Models\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Stubs\Models\UploadFilesStub;
use Tests\TestCase;

class UploadFilesUnitTest extends TestCase
{
    private UploadFilesStub $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new UploadFilesStub();
    }

    public function testUploadFile()
    {
        Storage::fake();
        $file = UploadedFile::fake()->create('video.mp4');
        $this->model->uploadFile($file);
        Storage::assertExists(sprintf('1/%s', $file->hashName()));
    }

    public function testUploadFiles()
    {
        Storage::fake();
        $file1 = UploadedFile::fake()->create('video1.mp4');
        $file2 = UploadedFile::fake()->create('video2.mp4');
        $this->model->uploadFiles([$file1, $file2]);
        Storage::assertExists(sprintf('1/%s', $file1->hashName()));
        Storage::assertExists(sprintf('1/%s', $file2->hashName()));
    }
}
