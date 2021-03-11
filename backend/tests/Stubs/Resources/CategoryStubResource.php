<?php
/**
 * Created by PhpStorm.
 * User: Herval
 * Date: 27/02/2021
 * Time: 18:50
 */

namespace Tests\Stubs\Resources;


use Illuminate\Http\Resources\Json\JsonResource;

class CategoryStubResource extends JsonResource
{
    public function toArray($request)
    {
        return parent::toArray($request);
    }
}
