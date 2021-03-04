<?php

namespace Tests\Feature\Http\Controllers\Api;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use App\Models\Category;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations;
    use TestValidations;
    use TestSaves;

    private $category;

    protected function setUp() : void
    {
        parent::setUp();
        $this->category = factory(Category::class)->create();
    }

    public function testIndex()
    {
        $response = $this->get(route('categories.index'));

        $response->assertStatus(200)
               ->assertJson([$this->category->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('categories.show', ['category' => $this->category->id]));

        $response->assertStatus(200)
               ->assertJson($this->category->toArray());
    }

    public function testInvalidationData()
    {
        $data = ['name' => ''];
        $this->assertInvalidationStoreAction($data, 'required');
        $this->assertInvalidationUpdateAction($data, 'required');

        $data = ['name' => str_repeat('a', 256)];
        $this->assertInvalidationStoreAction($data,'max.string', ['max' => 255]);
        $this->assertInvalidationUpdateAction($data, 'max.string', ['max' => 255]);

        $data = ['is_active' => 'a'];
        $this->assertInvalidationStoreAction($data, 'boolean');
        $this->assertInvalidationUpdateAction($data, 'boolean');
    }

    public function testStore()
    {
        $data = ['name' => 'test'];
        $response = $this->assertStore($data, $data + ['description' => null, 'is_active' => true, 'deleted_at' => null]);
        $response->assertJsonStructure(['created_at', 'updated_at']);

        $data = [
            'name' => 'test', 'is_active' => false, 'description' => 'test'
        ];
        $this->assertStore($data, $data + ['description' => 'test', 'is_active' => false]);
    }

    public function testUpdate()
    {
        $this->category = factory(Category::class)->create([
            'name' => 'test3',
            'is_active' => false,
        ]);

        $data = [
            'name' => 'test',
            'is_active' => true,
            'description' => 'test4'
        ];

        $response = $this->assertUpdate($data, $data + ['deleted_at' => null]);
        $response->assertJsonStructure(['created_at', 'updated_at']);

        $data = [
            'name' => 'test',
            'description' => ''
        ];

        $this->assertUpdate($data, array_merge($data, ['description' => null]));

        $data['description'] = 'test';

        $this->assertUpdate($data, array_merge($data, ['description' => 'test']));

        $data['description'] = null;

        $this->assertUpdate($data, array_merge($data, ['description' => null]));

    }

    public function testRemove()
    {

        $response = $this->json('DELETE', route('categories.destroy', ['category' => $this->category->id]));

        $response->assertStatus(204);

        $this->assertNull(Category::find($this->category->id));
    }

    protected function routeStore()
    {
        return route('categories.store');
    }

    protected function routeUpdate()
    {
        return route('categories.update', ['category' => $this->category->id]);
    }

    protected function model()
    {
        return Category::class;
    }
}