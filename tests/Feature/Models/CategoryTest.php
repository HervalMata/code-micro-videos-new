<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\Category;

class CategoryTest extends TestCase
{
    use DatabaseMigrations;

    public function testList()
    {
        factory(Category::class, 1)->create();

        $categories = Category::All();
        $this->assertCount(1, $categories);

        $keys = array_keys($categories->first()->getAttributes());
        $this->assertEqualsCanonicalizing(
            [
                'created_at', 'deleted_at', 'description', 'id',
                'is_active', 'name', 'updated_at'
            ], $keys
        );
    }

    public function testCreate()
    {
        $category = Category::create(['name' => 'test']);
        $category->refresh();

        $this->assertEquals('test', $category->name);
        $this->assertNull($category->description);
        $this->assertTrue($category->is_active);

        $category = Category::create([
            'name' => 'test',
            'description' => null
        ]);

        $this->assertNull($category->description);

        $category = Category::create([
            'name' => 'test',
            'description' => 'some description'
        ]);

        $this->assertEquals('some description', $category->description);

        $category = Category::create([
            'name' => 'test',
            'is_active' => false
        ]);

        $this->assertFalse($category->is_active);

        $category = Category::create([
            'name' => 'test',
            'is_active' => true
        ]);

        $this->assertTrue($category->is_active);
    }
}
