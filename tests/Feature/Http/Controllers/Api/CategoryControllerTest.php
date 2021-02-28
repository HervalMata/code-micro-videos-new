<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Resources\Json\JsonResource;
use Tests\TestCase;
use Tests\Traits\TestResources;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CategoryControllerTest extends TestCase
{
    use DatabaseMigrations;
    use TestValidations;
    use TestSaves;
    use TestResources;

    private $category;

    private $serializedFields = [
        'id', 'name', 'description', 'is_active', 'created_at', 'updated_at', 'deleted_at'
    ];

    protected function setUp() : void
    {
        parent::setUp();
        $this->category = factory(Category::class)->create();
    }

    public function testIndex()
    {
        $response = $this->get(route('categories.index'));

        $response->assertStatus(200)
               ->assertJson([$this->category->toArray()])
            ->assertJsonStructure([
                'data' => [
                    '*' => $this->serializedFields
                    ],
                'links' => [],
                'meta' => []
            ]);
        $resource = new CategoryResource::collection(collect($this->category));
        $this->assertResource($response, $resource);
    }

    public function testShow()
    {
        $response = $this->get(route('categories.show', ['category' => $this->category->id]));

        $response->assertStatus(200)
                ->assertJsonStructure([
                    'data' => $this->serializedFields
                ])
               ->assertJson([ 'data' => $this->category->toArray() ]);
        $id = $response->json('data.id');
        $resource = new CategoryResource(Category::find($id));
        $this->assertResource($response, $resource);
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
        $response->assertJsonStructure(
            [
                'data' => $this->serializedFields
            ]
        );

        $data = [
            'name' => 'test', 'is_active' => false, 'description' => 'test'
        ];
        $this->assertStore($data, $data + ['description' => 'test', 'is_active' => false]);
        $id = $response->json('data.id');
        $resource = new CategoryResource(Category::find($id));
        $this->assertResource($response, $resource);
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
        $response->assertJsonStructure([
            'data' => $this->serializedFields
        ]);
        $id = $response->json('data.id');
        $resource = new CategoryResource(Category::find($id));
        $this->assertResource($response, $resource);

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
