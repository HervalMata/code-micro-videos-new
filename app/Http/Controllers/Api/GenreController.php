<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\BasicCrudController;
use App\Models\Genre;
use Illuminate\Http\Request;

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
            'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL',
            'is_active' => 'boolean'
        ];
    }

    protected function rulesUpdate()
    {
        return $this->rulesStore();
    }

    public function store(Request $request)
    {
        $validateData = $this->validate($request, $this->rulesStore());
        $self = $this;
        $obj = \DB::transaction(function () use ($request, $validateData, $self) {
            $obj = $this->model()::create($validateData);
            $self->handleRelations($obj, $request);
            return $obj;
        });

        $obj->refresh();
        return $obj;
    }

    public function update(Request $request, $id)
    {
        $obj = $this->findOrFail($id);
        $validateData = $this->validate($request, $this->rulesUpdate());
        $self = $this;
        $obj = \DB::transaction(function () use ($request, $validateData, $self, $obj) {
            $obj->update($validateData);
            $self->handleRelations($obj, $request);
            return $obj;
        });

        return $obj;
    }

    protected function handleRelations($obj, Request $request)
    {
        $obj->categories()->sync($request->get('categories_id'));
    }
}
