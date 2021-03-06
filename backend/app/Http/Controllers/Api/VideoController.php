<?php

namespace App\Http\Controllers\Api;

use App\Http\Resources\VideoResource;
use App\Models\Video;
use App\Rules\GenresHasCategoriesRule;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class VideoController extends BasicCrudController
{

    private $rules;

    public function __construct()
    {
        $this->rules = [
            'title' => 'required|max:255',
            'description' => 'required',
            'year_launched' => 'required|date_format:Y',
            'opened' => 'boolean',
            'rating' => 'required|in:' . implode(',', Video::RATING_LIST),
            'duration' => 'required|integer',
            'categories_id' => 'required|array|exists:categories,id,deleted_at,NULL',
            'genres_id' => ['required','array','exists:cast_members,id,deleted_at,NULL'],
            'cast_members_id' => ['required','array','exists:genres,id,deleted_at,NULL'],
            'video_file' => 'mimetypes:video/mp4|max:' . Video::VIDEO_FILE_MAX_FILE,
            'thumb_file' => 'image|max:' . Video::THUMB_FILE_MAX_FILE,
            'banner_file' => 'image|max:' . Video::BANNER_FILE_MAX_FILE,
            'trailler_file' => 'mimetypes:video/mp4|max:' . Video::TRAILER_FILE_MAX_FILE
        ];
    }

    protected function model()
    {
        return Video::class;
    }

    protected function rulesUpdate()
    {
        return $this->rules;
    }

    protected function rulesStore()
    {
        return $this->rules;
    }

    protected function resourceCollection()
    {
        return $this->resource();
    }

    protected function resource()
    {
        return VideoResource::class;
    }

    protected function queryBuilder(): Builder
    {
        return parent::queryBuilder()->with(['genres, categories']);
    }


    protected function addRuleIfGenreHasCatagories(Request $request)
    {
        $categoriesId = $request->get('categories_id');
        $categoriesId = is_array($categoriesId) ? $categoriesId : [];
        $this->rules['genres_id'][] = new GenresHasCategoriesRule($categoriesId);
    }

    public function store(Request $request)
    {
        $this->addRuleIfGenreHasCatagories($request);
        $validateData = $this->validate($request, $this->rulesStore());
        $obj = $this->model()::create($validateData);
        $obj->refresh();
        $resource = $this->resource();
        return new $resource($obj);
    }

    public function update(Request $request, $id)
    {
        $obj = $this->findOrFail($id);
        $this->addRuleIfGenreHasCatagories($request);
        $validateData = $this->validate($request, $this->rulesUpdate());
        $obj->update($validateData);
        $obj->refresh();
        $resource = $this->resource();
        return new $resource($obj);
    }
}
