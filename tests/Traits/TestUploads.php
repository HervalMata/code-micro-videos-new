<?php
/**
 * Created by PhpStorm.
 * User: Herval
 * Date: 26/02/2021
 * Time: 16:17
 */
declare(strict_types=1);
namespace Tests\Traits;

use Illuminate\Foundation\Testing\TestResponse;
use Exception;
use Illuminate\Http\UploadedFile;

trait TestUploads
{
    protected function assertInvalidationFile($field, $extension, $maxsize, $rule, $ruleParams = [])
    {
        $routes = [
            [
                'method' => 'POST',
                'route' => $this->routeStore()
            ],
            [
                'method' => 'PUT',
                'route' => $this->routeUpdate()
            ]
        ];

        foreach ($routes as $route) {
            $file = UploadedFile::fake()->create("$field.1$extension");
            $response = $this->json($route['method'], $route['route'], [
                $field => $file
            ]);
            $this->assertInvalidationFields($response, [$field], $rule, $ruleParams);

            $file = UploadedFile::fake()->create("$field.$extension")->size($maxsize + 1);
            $response = $this->json($route['method'], $route['route'], [
                $field => $file
            ]);
            $this->assertInvalidationFields($response, [$field], 'max.file', ['max' => $maxsize]);
        }
    }
}