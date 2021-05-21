<?php

namespace Tests\Feature\Models\Traits;

use Tests\Stubs\Models\UploadFilesStub;
use Tests\TestCase;

class UploadFilesTest extends TestCase
{
    private UploadFilesStub $model;

    protected function setUp(): void
    {
        parent::setUp();
        $this->model = new UploadFilesStub();
        UploadFilesStub::dropTable();
        UploadFilesStub::createTable();
    }

    public function testMakeOldFieldsOnSaving()
    {
        $this->model->fill([
            'name' => 'test',
            'file1' => 'test1.mp4',
            'file2' => 'test2.mp4',
        ])->save();
        $this->assertCount(0, $this->model->oldFiles);

        $this->model->update([
            'name' => 'test_updated',
            'file2' => 'test2_updated.mp4'
        ]);
        $this->assertEqualsCanonicalizing(['test2.mp4'], $this->model->oldFiles);
    }

    public function testMakeOldFilesNullOnSaving()
    {
        $this->model->fill([
            'name' => 'test',
        ])->save();

        $this->model->update([
            'name' => 'test_updated',
            'file2' => 'test2_updated.mp4'
        ]);
        $this->assertEqualsCanonicalizing([], $this->model->oldFiles);
    }

}
