<?php

namespace App\Http\Controllers\Api;

use App\Models\Video;

class VideoController extends BasicCrudController
{

    protected function model()
    {
        return Video::class;
    }

    protected function rulesUpdate()
    {
        return $this->rulesStore();
    }

    protected function rulesStore()
    {
        return [];
    }
}
