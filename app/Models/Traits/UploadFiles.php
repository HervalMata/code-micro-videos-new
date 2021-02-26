<?php
/**
 * Created by PhpStorm.
 * User: Herval
 * Date: 26/02/2021
 * Time: 13:59
 */

namespace App\Models\Traits;


use Illuminate\Http\UploadedFile;

trait UploadFiles
{
    protected abstract function uploadDir();

    /**
     * @param UploadedFile $files
     */
    public function uploadFiles(array $files)
    {
        foreach ($files as $file) {
            $this->uploadFile($file);
        }
    }

    /**
     * @param UploadedFile $files
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
}
