<?php
/**
 * Created by PhpStorm.
 * User: Herval
 * Date: 27/02/2021
 * Time: 18:17
 */

namespace Tests\Traits;


use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Http\Resources\Json\JsonResource;

trait TestResources
{
    protected function assertResource(TestResponse $response, JsonResource $resource)
    {
        $response->assertJson($resource->response()->getData(true));
    }
}
