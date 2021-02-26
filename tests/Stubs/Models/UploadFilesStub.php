<?php
/**
 * Created by PhpStorm.
 * User: Herval
 * Date: 26/02/2021
 * Time: 14:04
 */

namespace Tests\Stubs\Models;


use App\Models\Traits\UploadFiles;
use Illuminate\Database\Eloquent\Model;

class UploadFilesStub extends Model
{
    use UploadFiles;

    protected function uploadDir()
    {
        return '1';
    }
}
