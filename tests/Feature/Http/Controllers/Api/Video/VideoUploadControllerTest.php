<?php

namespace Tests\Feature\Http\Controllers\Api\Video;

use App\Models\Video;
use Illuminate\Http\Response;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\Traits\TestUploads;
use Tests\Traits\TestValidations;

class VideoUploadControllerTest extends BaseVideoControllerTestCase
{
    use TestValidations;
    use TestUploads;

    public function testInvalidateThumbField()
    {
        $this->assertInvalidationFile('thumb_file', 'jpg', Video::THUMB_FILE_MAX_SIZE, 'image');
    }

    public function testInvalidateBannerField()
    {
        $this->assertInvalidationFile('banner_file', 'jpg', Video::BANNER_FILE_MAX_SIZE, 'image');
    }

    public function testInvalidateTrailerField()
    {
        $this->assertInvalidationFile(
            'trailer_file',
            'mp4',
            Video::TRAILER_FILE_MAX_SIZE,
            'mimetypes', ['values' => 'video/mp4']
        );
    }

    public function testInvalidateVideoField()
    {
        $this->assertInvalidationFile(
            'video_file',
            'mp4',
            Video::VIDEO_FILE_MAX_SIZE,
            'mimetypes', ['values' => 'video/mp4']
        );
    }

    public function testStoreWithFiles()
    {
        Storage::fake();
        $files = $this->getFiles();

        $response = $this->json('POST', $this->routeStore(), $this->sendData + $files);

        $response->assertStatus(Response::HTTP_CREATED);
        $this->assertFilesOnPersist($response, $files);
    }

    public function testUpdateWithFiles()
    {
        Storage::fake();
        $files = $this->getFiles();

        $response = $this->json('PUT', $this->routeUpdate(), $this->sendData + $files);

        $response->assertStatus(Response::HTTP_OK);
        $this->assertFilesOnPersist($response, $files);

        $newFiles = [
            'thumb_file' => UploadedFile::fake()->image('thumb_file.jpg'),
            'video_file' => UploadedFile::fake()->create('video_file.mp4'),
        ];

        $response = $this->json('PUT', $this->routeUpdate(), $this->sendData + $newFiles);
        $response->assertStatus(Response::HTTP_OK);
        $this->assertFilesOnPersist($response, Arr::except($files, ['thumb_file', 'video_file']) + $newFiles);

        $id = $response->json('id') ?? $response->json('data.id');
        $model = Video::find($id);
        Storage::assertMissing($model->relativeFilePath($files['thumb_file']->hashName()));
        Storage::assertMissing($model->relativeFilePath($files['video_file']->hashName()));
    }

    protected function assertFilesOnPersist(TestResponse $response, $files)
    {
        $id = $response->json('id') ?? $response->json('data.id');
        $model = Video::find($id);
        $this->assertFilesExistsInStorage($model, $files);
        $this->assertIfFilesUrlExists($model, $response);
    }

    protected function getFiles(): array
    {
        return [
            'thumb_file' => UploadedFile::fake()->image('thumb_file.jpg'),
            'banner_file' => UploadedFile::fake()->image('banner_file.jpg'),
            'trailer_file' => UploadedFile::fake()->create('trailer_file.mp4'),
            'video_file' => UploadedFile::fake()->create('video_file.mp4'),
        ];
    }
}
