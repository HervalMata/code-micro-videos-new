<?php

namespace App\Models;

use App\Models\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CastMember extends Model
{
    use SoftDeletes;

    use Uuid;

    const TYPE_DIRECTOR = 1;
    const TYPE_ACTOR = 2;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name', 'type'
    ];

    protected $casts = [
        'id' => 'string'
    ];

    public $incrementing = false;
}
