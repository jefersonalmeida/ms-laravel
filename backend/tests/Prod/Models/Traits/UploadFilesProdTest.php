<?php

namespace Tests\Prod\Models\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Storage;
use Tests\Stubs\Models\UploadFilesStub;
use Tests\TestCase;
use Tests\Traits\TestProd;
use Tests\Traits\TestStorages;

class UploadFilesProdTest extends TestCase
{
    use TestStorages;
    use TestProd;

    private UploadFilesStub $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->skipTestIfNotProd();
        $this->model = new UploadFilesStub();
        Config::set('filesystems.default', 'gcs');
        $this->deleteAllFiles();
    }

    public function testUploadFile()
    {
        $file = UploadedFile::fake()->create('video.mp4');
        $this->model->uploadFile($file);
        Storage::assertExists(sprintf('1/%s', $file->hashName()));
    }

    public function testUploadFiles()
    {
        $file1 = UploadedFile::fake()->create('video1.mp4');
        $file2 = UploadedFile::fake()->create('video2.mp4');
        $this->model->uploadFiles([$file1, $file2]);
        Storage::assertExists(sprintf('1/%s', $file1->hashName()));
        Storage::assertExists(sprintf('1/%s', $file2->hashName()));
    }

    public function testDeleteFile()
    {
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
        $file1 = UploadedFile::fake()->create('video1.mp4');
        $file2 = UploadedFile::fake()->create('video2.mp4');
        $this->model->uploadFiles([$file1, $file2]);
        $this->model->deleteFiles([$file1->hashName(), $file2]);
        Storage::assertMissing(sprintf('1/%s', $file1->hashName()));
        Storage::assertMissing(sprintf('1/%s', $file2->hashName()));
    }

    public function testDeleteOldFiles()
    {
        $file1 = UploadedFile::fake()->create('video1.mp4')->size(1);
        $file2 = UploadedFile::fake()->create('video2.mp4')->size(1);
        $this->model->uploadFiles([$file1, $file2]);
        $this->model->deleteOldFiles();
        $this->assertCount(2, Storage::allFiles());

        $this->model->oldFiles = [$file1->hashName()];
        $this->model->deleteOldFiles();
        Storage::assertMissing(sprintf('1/%s', $file1->hashName()));
        Storage::assertExists(sprintf('1/%s', $file2->hashName()));
    }
}
