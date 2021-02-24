<?php

namespace Tests\Stubs\Controllers;

use App\Http\Controllers\Api\BasicCrudController;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Tests\Stubs\Models\CategoryStub;
use Tests\TestCase;
use Illuminate\Http\Request;

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

        $controller = new CategoryControllerStub();
        $this->assertEquals([$category->toArray()], $this->controller->index()->toArray());
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
        $obj = $this->controller->store($request);
        $this->assertEquals(
            CategoryStub::find(1)->toArray(),
            $obj->toArray()
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

        $result = $reflectionMethod->invokeArgs($this->controller, [$category->id]);

        $this->assertInstanceOf(CategoryStub::class, $result);
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

        $result = $this->controller->show($category->id);

        $this->assertEquals($category->toArray(), $result->toArray());
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

        $obj = $this->controller->update($request, $category->id);

        $this->assertEquals(
            CategoryStub::find(1)->toArray(),
            $obj->toArray()
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
