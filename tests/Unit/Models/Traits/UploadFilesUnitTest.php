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

    public function testDeleteFile()
    {
        Storage::fake();
        $file = UploadedFile::fake()->create('video.mp4');
        $this->model->uploadFile($file);
        $filename = $file->hashName();
        $this->model->deleteFile($filename);
        Storage::assertMissing(sprintf('1/%s', $filename));


        $file = UploadedFile::fake()->create('video.mp4');
        $this->model->uploadFile($file);
        $this->model->deleteFile($file);
        Storage::assertMissing(sprintf('1/%s', $file->hashName()));
    }

    public function testDeleteFiles()
    {
        Storage::fake();
        $file1 = UploadedFile::fake()->create('video1.mp4');
        $file2 = UploadedFile::fake()->create('video2.mp4');
        $this->model->uploadFiles([$file1, $file2]);
        $this->model->deleteFiles([$file1->hashName(), $file2]);
        Storage::assertMissing(sprintf('1/%s', $file1->hashName()));
        Storage::assertMissing(sprintf('1/%s', $file2->hashName()));
    }

    public function testExtractFiles()
    {
        Storage::fake();

        $attributes = [];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertCount(0, $attributes);
        $this->assertCount(0, $files);

        $attributes = ['file1' => 'test'];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertCount(1, $attributes);
        $this->assertEquals($attributes, $attributes);
        $this->assertCount(0, $files);

        $attributes = ['file1' => 'test', 'file2' => 'test'];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertCount(2, $attributes);
        $this->assertEquals($attributes, $attributes);
        $this->assertCount(0, $files);

        $file1 = UploadedFile::fake()->create('video1.mp4');
        $attributes = ['file1' => $file1, 'other' => 'test'];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertCount(2, $attributes);
        $this->assertEquals(['file1' => $file1->hashName(), 'other' => 'test'], $attributes);
        $this->assertEquals([$file1], $files);

        $file2 = UploadedFile::fake()->create('video2.mp4');
        $attributes = ['file1' => $file1, 'file2' => $file2, 'other' => 'test'];
        $files = UploadFilesStub::extractFiles($attributes);
        $this->assertCount(3, $attributes);
        $this->assertEquals([
            'file1' => $file1->hashName(),
            'file2' => $file2->hashName(),
            'other' => 'test'
        ], $attributes);
        $this->assertEquals([$file1, $file2], $files);
    }
}
