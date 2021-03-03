<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Models\Traits\Uuid;

class Genre extends Model
{
    use SoftDeletes;

    use Uuid;

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name', 'is_active'
    ];

    protected $casts = [
        'id' => 'string',
        'is_active' => 'boolean'
    ];

    public $incrementing = false;
}
