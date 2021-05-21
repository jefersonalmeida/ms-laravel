<?php

namespace Tests\Feature\Models\Video;

use App\Models\Video;
use Illuminate\Database\Events\TransactionCommitted;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
use Tests\Exceptions\TestException;
use Throwable;

class VideoUploadTest extends BaseVideoTestCase
{
    /**
     * @throws Throwable
     */
    public function testCreateWithFiles()
    {
        Storage::fake();
        $model = Video::create(
            $this->data + [
                'thumb_file' => UploadedFile::fake()->image('thumb.jpg'),
                'video_file' => UploadedFile::fake()->create('video.mp4'),
            ]
        );
        Storage::assertExists(sprintf('%s/%s', $model->id, $model->thumb_file));
        Storage::assertExists(sprintf('%s/%s', $model->id, $model->video_file));
    }

    /**
     * @throws Throwable
     */
    public function testCreateIfRollbackFiles()
    {
        Storage::fake();
        Event::listen(TransactionCommitted::class, function () {
            throw new TestException();
        });

        $hasError = false;
        try {
            Video::create(
                $this->data + [
                    'thumb_file' => UploadedFile::fake()->image('thumb.jpg'),
                    'video_file' => UploadedFile::fake()->create('video.mp4'),
                ]
            );
        } catch (TestException $e) {
            $this->assertCount(0, Storage::allFiles());
            $hasError = true;
        }
        $this->assertTrue($hasError);
    }

    /**
     * @throws Throwable
     */
    public function testUpdateWithFiles()
    {
        Storage::fake();
        /** @var Video $model */
        $model = Video::factory()->create();
        $thumbFile = UploadedFile::fake()->image('thumb.jpg');
        $videoFile = UploadedFile::fake()->create('video.mp4');

        $model->update($this->data + ['thumb_file' => $thumbFile, 'video_file' => $videoFile]);
        Storage::assertExists(sprintf('%s/%s', $model->id, $model->thumb_file));
        Storage::assertExists(sprintf('%s/%s', $model->id, $model->video_file));

        $newVideoFile = UploadedFile::fake()->create('video.mp4');
        $model->update($this->data + ['video_file' => $newVideoFile]);
        Storage::assertExists(sprintf('%s/%s', $model->id, $thumbFile->hashName()));
        Storage::assertExists(sprintf('%s/%s', $model->id, $newVideoFile->hashName()));
        Storage::assertMissing(sprintf('%s/%s', $model->id, $videoFile->hashName()));
    }

    /**
     * @throws Throwable
     */
    public function testUpdateIfRollbackFiles()
    {
        Storage::fake();
        /** @var Video $model */
        $model = Video::factory()->create();

        Event::listen(TransactionCommitted::class, function () {
            throw new TestException();
        });

        $hasError = false;
        try {
            $model->update(
                $this->data + [
                    'thumb_file' => UploadedFile::fake()->image('thumb.jpg'),
                    'video_file' => UploadedFile::fake()->create('video.mp4'),
                ]
            );
        } catch (TestException $e) {
            $this->assertCount(0, Storage::allFiles());
            $hasError = true;
        }
        $this->assertTrue($hasError);
    }
}
