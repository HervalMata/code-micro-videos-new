<?php
/**
 * Created by PhpStorm.
 * User: Herval
 * Date: 26/02/2021
 * Time: 13:59
 */

namespace App\Models\Traits;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Arr;

trait UploadFiles
{
    public $oldFiles = [];
    protected abstract function uploadDir();

    public static function bootUploadFiles()
    {
        static::updating(function (Model $model) {
            $fieldsUpdated = array_keys($model->getDirty());
            $filesUpdated = array_intersect($fieldsUpdated, self::$fileFields);
            $filesFiltered = Arr::where($filesUpdated, function ($fileField) use ($model) {
                return $model->getOriginal($fileField);
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

    /**
     * @param UploadedFile $file
     */
    public function uploadFile(UploadedFile $file)
    {
        $file->store($this->uploadDir());
    }

    /**
     * @param UploadedFile[] $files
     */
    public function deletefiles(array $files)
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
        $fileName = $file instanceof UploadedFile ? $file->hashName() : $file;
        \Storage::delete("{$this->uploadDir()}/{$fileName}");
    }

    public static function extractFiles(array &$attributes = [])
    {
        $files = [];
        foreach (self::$fileFields as $file) {
            if (isset($attributes[$file]) && $attributes[$file] instanceof UploadedFile) {
                $files[] = $attributes[$file];
                $attributes[$file] = $attributes[$file]->hashName();
            }
            return $files;
        }
    }

    public function deleteOldFiles()
    {
        $this->deletefiles($this->oldFiles);
    }

    public function relativeFilePath($value)
    {
        return "{$this->uploadDir()}/{$value}";
    }

    protected function getFileUrl($fileName)
    {
        return \Storage::url($this->relativeFilePath($fileName));
    }
}
