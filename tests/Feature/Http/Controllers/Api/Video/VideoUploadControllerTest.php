<?php

namespace Tests\Feature\Http\Controllers\Api\Video;

use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\Traits\TestUploads;
use Tests\Traits\TestValidations;

class VideoUploadControllerTest extends BaseVideoControllerTestCase
{
    use TestValidations;
    use TestUploads;

    public function testInvalidateVideoField()
    {
        $this->assertInvalidationFile('video_file', 'mp4', 12, 'mimetypes', ['values' => 'video/mp4']);
    }

    public function testStoreWithFiles()
    {
        Storage::fake();
        $files = $this->getFiles();

        $response = $this->json('POST', $this->routeStore(), $this->sendData + $files);

        $response->assertStatus(Response::HTTP_CREATED);
        $id = $response->json('id');
        foreach ($files as $file) {
            Storage::assertExists(sprintf('%s/%s', $id, $file->hashName()));
        }
    }

    public function testUpdateWithFiles()
    {
        Storage::fake();
        $files = $this->getFiles();

        $response = $this->json('PATCH', $this->routeUpdate(), $this->sendData + $files);

        $response->assertStatus(Response::HTTP_OK);
        $id = $response->json('id');
        foreach ($files as $file) {
            Storage::assertExists(sprintf('%s/%s', $id, $file->hashName()));
        }
    }

    protected function getFiles(): array
    {
        return [
            'video_file' => UploadedFile::fake()->create('video_file.mp4'),
        ];
    }
}
