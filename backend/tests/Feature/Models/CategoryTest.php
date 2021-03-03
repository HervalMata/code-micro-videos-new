<?php

namespace Tests\Feature\Models;

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
        $this->assertUuidV4($category->id);

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

    public function testUpdate()
    {
        $category = factory(Category::class)->create([
            'description' => 'some description',
            'is_active' => false
        ])->first();

        $data = [
            'name' => 'test2',
            'description' => 'some description more',
            'is_active' => true
        ];

        $category->update($data);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $category->{$key});
        }
    }

    public function testRemove()
    {
        $categories = factory(Category::class, 5)->create();
        $categories[0]->delete();
        $total = Category::count();
        $this->assertEquals(4, $total);
        $this->assertNull(Category::find($categories[0]->id));
        $categories[0]->restore();
        $this->assertNotEmpty(Category::find($categories[0]->id));
    }
}
