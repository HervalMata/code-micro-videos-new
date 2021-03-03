<?php

namespace App\Http\Controllers\Api;

use App\Models\Category;
use App\Http\Controllers\Api\BasicCrudController;

class CategoryController extends BasicCrudController
{
    protected function model()
    {
        return Category::class;
    }

    protected function rulesStore()
    {
        return [
            'name' => 'required|max:255',
            'description' => 'nullable',
            'is_active' => 'boolean'
        ];
    }

    protected function rulesUpdate()
    {
        return $this->rulesStore();
    }
}
