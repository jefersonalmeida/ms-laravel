<?php

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;

trait UploadFiles
{
    public array $oldFiles = [];

    protected abstract function uploadDir(): string;

    public abstract static function fileFields(): array;

    public static function bootUploadFiles()
    {
        static::updating(function (Model $model) {
            $fieldsUpdated = array_keys($model->getDirty());
            $filesUpdated = array_intersect($fieldsUpdated, self::fileFields());
            $filesFiltered = Arr::where($filesUpdated, function ($fileField) use ($model) {
                return $model->getOriginal($fileField) !== null;
            });
            $model->oldFiles = array_map(function ($fileField) use ($model) {
                return $model->getOriginal($fileField);
            }, $filesFiltered);
        });
    }

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

    public function deleteOldFiles()
    {
        $this->deleteFiles($this->oldFiles);
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

    public function relativeFilePath($value): string
    {
        return sprintf('%s/%s', $this->uploadDir(), $value);
    }

    public function getFileUrl($filename): string
    {
        return Storage::url($this->relativeFilePath($filename));
    }
}
