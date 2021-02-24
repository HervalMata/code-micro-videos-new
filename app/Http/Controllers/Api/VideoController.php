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
        return [
            'title' => 'required|max:255',
            'description' => 'required',
            'year_launched' => 'required|date_format:Y',
            'opened' => 'boolean',
            'rating' => 'required|in:' . implode(',', Video::RATING_LIST),
            'duration' => 'required|integer',
        ];
    }
}
