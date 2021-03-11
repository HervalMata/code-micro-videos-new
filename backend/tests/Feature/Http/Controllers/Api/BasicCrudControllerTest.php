<?php

namespace Tests\Stubs\Controllers;

use App\Http\Controllers\Api\BasicCrudController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Tests\Stubs\Models\CategoryStub;
use Tests\TestCase;

class BasicCrudControllerStub extends TestCase
{
    private $controller;

    protected function setUp(): void
    {
        parent::setUp();
        CategoryStub::dropTable();
        CategoryStub::createTable();
        $this->controller = new CategoryControllerStub();
    }

    protected function tearDown(): void
    {
        CategoryStub::dropTable();
        parent::tearDown();
    }

    public function testIndex()
    {
        $category = CategoryStub::create([
            'name' => 'testName', 'description' => 'testdescription'
        ]);

        $resource = $this->controller->index();
        $serialized = $resource->response()->getData(true);

        $this->assertEquals([$category->toArray()], $serialized['data']);
        $this->assertArrayHasKey('meta', $serialized);
        $this->assertArrayHasKey('links', $serialized);
    }

    public function testInvalidationDataInStore()
    {
        $request = $this->mock(Request::class);
        $request->shouldReceive('all')->once()->andReturn(['name' => '']);
        $this->expectException(\Illuminate\Validation\ValidationException::class);
        $this->controller->store($request);
    }

    public function testStore()
    {
        $request = $this->mock(Request::class);
        $request->shouldReceive('all')->once()->andReturn([
            'name' => 'test',
            'description' => 'test_desc'
        ]);
        $resource = $this->controller->store($request);
        $serialized = $resource->response()->getData(true);
        $this->assertEquals(
            CategoryStub::find(1)->toArray(),
            $serialized['data']
        );
    }

    public function testIfFindOrFailFetchModel()
    {
        $category = CategoryStub::create([
            'name' => 'testName', 'description' => 'testdescription'
        ]);

        $reflectionClass = new \ReflectionClass(BasicCrudController::class);

        $reflectionMethod = $reflectionClass->getMethod('findOrFail');
        $reflectionMethod->setAccessible(true);

        $resource = $reflectionMethod->invokeArgs($this->controller, [$category->id]);

        $this->assertInstanceOf(CategoryStub::class, $resource);
    }

    public function testIfFindOrFailExceptionWhenIdInvalid()
    {
        $reflectionClass = new \ReflectionClass(BasicCrudController::class);

        $reflectionMethod = $reflectionClass->getMethod('findOrFail');
        $reflectionMethod->setAccessible(true);

        $this->expectException(ModelNotFoundException::class);
        $reflectionMethod->invokeArgs($this->controller, [0]);
    }

    public function testShow()
    {
        $category = CategoryStub::create([
            'name' => 'testName', 'description' => 'testdescription'
        ]);

        $resource = $this->controller->show($category->id);
        $serialized = $resource->response()->getData(true);
        $this->assertEquals($category->toArray(), $serialized['data']);
    }

    public function testUpdate()
    {
        $category = CategoryStub::create([
            'name' => 'testName', 'description' => 'testdescription'
        ]);

        $request = $this->mock(Request::class);
        $request->shouldReceive('all')->once()->andReturn([
            'name' => 'test',
            'description' => 'test_desc'
        ]);

        $resource = $this->controller->update($request, $category->id);
        $serialized = $resource->response()->getData(true);
        $this->assertEquals(
            CategoryStub::find(1)->toArray(),
            $serialized['data']
        );
    }

    public function testDestroy()
    {
        $category = CategoryStub::create([
            'name' => 'testName', 'description' => 'testdescription'
        ]);

        $response = $this->controller->destroy($category->id);

        $this->createTestResponse($response)->assertStatus(204);
        $this->assertCount(0, CategoryStub::all());
    }
}
