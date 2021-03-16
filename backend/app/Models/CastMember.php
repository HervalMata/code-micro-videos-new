<?php

namespace App\Models;

use App\ModelFilters\CastMemberFilter;
use App\Models\Traits\Uuid;
use EloquentFilter\Filterable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class CastMember extends Model
{
    use SoftDeletes;
    use Filterable;
    use Uuid;

    const TYPE_DIRECTOR = 1;
    const TYPE_ACTOR = 2;

    public static $types = [
        CastMember::TYPE_ACTOR,
        CastMember::TYPE_DIRECTOR,
    ];

    protected $dates = ['deleted_at'];

    protected $fillable = [
        'name', 'type'
    ];

    protected $casts = [
        'id' => 'string'
    ];

    public $incrementing = false;

    public function modelFilter()
    {
        return $this->provideFilter(CastMemberFilter::class);
    }
}
