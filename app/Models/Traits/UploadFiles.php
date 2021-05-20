<?php

namespace App\Models\Traits;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

trait UploadFiles
{
    protected abstract function uploadDir(): string;

    protected abstract static function fileFields(): array;

    /**
     * @param UploadedFile[] $files
     */
    public function uploadFiles(array $files)
    {
        foreach ($files as $file) {
            $this->uploadFile($file);
        }
    }

    public function uploadFile(UploadedFile $file)
    {
        $file->store($this->uploadDir());
    }

    public function deleteFiles(array $files)
    {
        foreach ($files as $file) {
            $this->deleteFile($file);
        }
    }

    /**
     * @param string|UploadedFile $file
     */
    public function deleteFile($file)
    {
        $filename = $file instanceof UploadedFile ? $file->hashName() : $file;
        Storage::delete(sprintf('%s/%s', $this->uploadDir(), $filename));
    }

    public static function extractFiles(array &$attributes = []): array
    {
        $files = [];
        foreach (self::fileFields() as $fileField) {
            if (isset($attributes[$fileField]) && $attributes[$fileField] instanceof UploadedFile) {
                $files[] = $attributes[$fileField];
                $attributes[$fileField] = $attributes[$fileField]->hashName();
            }
        }
        return $files;
    }
}
