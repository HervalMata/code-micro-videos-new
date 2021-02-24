<?php

namespace App\Models;

use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

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
        'year_launched' => 'boolean',
        'opened' => 'boolean',
        'duration' => 'integer'
    ];
}
