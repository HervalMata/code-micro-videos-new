<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BasicCrudController;
use App\Models\Genre;

class GenreController extends BasicCrudController
{
    protected function model()
    {
        return Genre::class;
    }

    protected function rulesStore()
    {
        return [
            'name' => 'required|max:255',
            'is_active' => 'boolean'
        ];
    }

    protected function rulesUpdate()
    {
        return $this->rulesStore();
    }
}