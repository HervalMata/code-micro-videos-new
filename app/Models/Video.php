<?php

namespace App\Models;

use App\Models\Traits\UploadFiles;
use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Exception;

class Video extends Model
{
    use SoftDeletes;
    use UploadFiles;
    use Uuid;

    const RATING_LIST = ['L', '10', '12', '14', '16', '18'];

    public $incrementing = false;
    protected $dates = ['deleted_at'];
    protected $fillable = [
        'title', 'description', 'year_launched',
        'opened', 'rating', 'duration', 'video_file'
    ];
    protected $casts = [
        'id' => 'string',
        'year_launched' => 'integer',
        'opened' => 'boolean',
        'duration' => 'integer'
    ];

    public static $fileFields = ['video_file'];

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
        $files = self::extractFiles($attributes);
        try {
            \DB::beginTransaction();
            /**
             * @var Video
             */
            $obj = static::query()->create($attributes);
            static::handleRelations($obj, $attributes);
            $obj->uploadFiles($files);
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
        $files = self::extractFiles($attributes);
        $oldFileName = $this->video_file;
        try {
            \DB::beginTransaction();
            $saved = parent::update($attributes, $options);
            static::handleRelations($this, $attributes);
            if ($saved) {
                $this->uploadFiles($files);
                if ($oldFileName) {
                    $this->deleteFile($oldFileName);
                }
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

    protected function uploadDir()
    {
        return $this->id;
    }
}
