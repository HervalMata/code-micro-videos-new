<?php

namespace App\Models;

use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Exception;

class Video extends Model
{
    use SoftDeletes;

    use Uuid;

    const RATING_LIST = ['L', '10', '12', '14', '16', '18'];

    public $incrementing = false;
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'title', 'description', 'year_launched',
        'opened', 'rating', 'duration'
    ];
    protected $casts = [
        'id' => 'string',
        'year_launched' => 'integer',
        'opened' => 'boolean',
        'duration' => 'integer'
    ];

    public function categories()
    {
        return $this->belongsToMany(Category::class)->withTrashed();
    }

    public function genres()
    {
        return $this->belongsToMany(Genre::class)->withTrashed();
    }

    public static function create(array $attributes = [])
    {
        try {
            \DB::beginTransaction();
            $obj = static::query()->create($attributes);
            static::handleRelations($obj, $attributes);
            \DB::commit();
            return $obj;
        } catch (Exception $e) {
            if (isset($obj)) {

            }
            \DB::rollBack();
            throw $e;
        }
    }

    public function update(array $attributes = [], array $options = [])
    {
        try {
            \DB::beginTransaction();
            $saved = parent::update($attributes, $options);
            static::handleRelations($this, $attributes);
            if ($saved) {

            }
            \DB::commit();
            return $saved;
        } catch (Exception $e) {
            \DB::rollBack();
            throw $e;
        }
    }

    public static function handleRelations(Video $video, $attributes)
    {
        if (isset($attributes['categories_id'])) {
            $video->categories()->sync($attributes['categories_id']);
        }

        if (isset($attributes['genres_id'])) {
            $video->genres()->sync($attributes['genres_id']);
        }

    }
}
