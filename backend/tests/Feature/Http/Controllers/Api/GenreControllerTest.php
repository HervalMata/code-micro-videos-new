<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Http\Controllers\Api\GenreController;
use App\Http\Resources\GenreResource;
use App\Models\Category;
use App\Models\Genre;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Tests\Exceptions\TestException;
use Tests\TestCase;
use Tests\Traits\TestResources;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class GenreControllerTest extends TestCase
{
    use DatabaseMigrations;
    use TestValidations;
    use TestSaves;
    use TestResources;

    private $genre;

    private $serializedFields = [
        'id', 'name', 'is_active', 'created_at', 'updated_at', 'deleted_at',
        'categories' => [
            '*' => [
                'id', 'name', 'description', 'is_active', 'created_at', 'updated_at', 'deleted_at'
            ]
        ]
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $this->genre = factory(Genre::class)->create();
    }

    public function testIndex()
    {
        $response = $this->get(route('genres.index'));

        $response->assertStatus(200)
            ->assertJson([$this->genre->toArray()])->assertJsonStructure([
                'data' => [
                    '*' => $this->serializedFields
                ],
                'links' => [],
                'meta' => []
            ]);
    }

    public function testShow()
    {
        $response = $this->get(route('genres.show', ['genre' => $this->genre->id]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => $this->serializedFields
            ])
            ->assertJson($this->genre->toArray());
    }

    public function testInvalidationData()
    {
        $data = ['name' => '', 'categories_id' => ''];
        $this->assertInvalidationStoreAction($data, 'required');
        $this->assertInvalidationUpdateAction($data, 'required');

        $data = ['name' => str_repeat('a', 256)];
        $this->assertInvalidationStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationUpdateAction($data, 'max.string', ['max' => 255]);

        $data = ['is_active' => 'a'];
        $this->assertInvalidationStoreAction($data, 'boolean');
        $this->assertInvalidationUpdateAction($data, 'boolean');

        $data = ['categories_id' => 'a'];
        $this->assertInvalidationStoreAction($data, 'array');
        $this->assertInvalidationUpdateAction($data, 'array');

        $data = ['categories_id' => [100]];
        $this->assertInvalidationStoreAction($data, 'exists');
        $this->assertInvalidationUpdateAction($data, 'exists');

        $category = factory(Category::class)->create();
        $category->delete();

        $data = ['categories_id' => [$category->id]];
        $this->assertInvalidationStoreAction($data, 'exists');
        $this->assertInvalidationUpdateAction($data, 'exists');
    }

    public function testStore()
    {
        $categories = factory(Category::class, 2)->create();
        $data = ['name' => 'test'];
        $extra = [
            'categories_id' => $categories->pluck('id')->toArray()
        ];
        $response = $this->assertStore($data + $extra, $data + ['is_active' => true, 'deleted_at' => null]);
        $response->assertJsonStructure(
            [
                'data' => $this->serializedFields
            ]
        );

        $this->assertHasCategory($response->json('data.id'), $categories[0]->id);

        $data = [
            'name' => 'test', 'is_active' => false
        ];
        $this->assertStore($data + $extra, $data + ['is_active' => false]);
        $genre = Genre::find($response->json('data.id'));
        $this->assertResource($response, new GenreResource($genre));
    }

    public function testUpdate()
    {
        $category = factory(Category::class)->create();
        $this->genre = factory(Genre::class)->create([
            'name' => 'test3',
            'is_active' => false,
        ]);

        $data = [
            'name' => 'test',
            'is_active' => true
        ];

        $response = $this->assertUpdate($data + ['categories_id' => [$category->id]], $data + ['deleted_at' => null]);
        $response->assertJsonStructure(
            [
                'data' => $this->serializedFields
            ]
        );

        $this->assertHasCategory($response->json('data.id'), $category->id);
        $genre = Genre::find($response->json('data.id'));
        $this->assertResource($response, new GenreResource($genre));

    }

    public function testRemove()
    {
        $response = $this->json('DELETE', route('genres.destroy', ['genre' => $this->genre->id]));

        $response->assertStatus(204);

        $this->assertNull(Genre::find($this->genre->id));
    }

    public function testSyncCategories()
    {
        $categories_id = factory(Category::class, 3)->create()->pluck('id')->toArray();
        $sendData = [
            'name' => 'test',
            'categories_id' => [$categories_id[0]]
        ];
        $response = $this->json('POST', $this->routeStore(), $sendData);
        $this->assertDatabaseHas('category_genre', [
            'genre_id' => $response->json('data.id'),
            'category_id' => $categories_id[0]
        ]);
        $sendData = [
            'name' => 'test',
            'categories_id' => [$categories_id[1], $categories_id[2]]
        ];
        $response = $this->json('PUT', route('genres.update', ['genre' => $response->json('data.id')]), $sendData);
        $this->assertDatabaseMissing('category_genre', [
            'genre_id' => $response->json('data.id'),
            'category_id' => $categories_id[0]
        ]);
        $this->assertDatabaseHas('category_genre', [
            'genre_id' => $response->json('data.id'),
            'category_id' => $categories_id[1]
        ]);
        $this->assertDatabaseHas('category_genre', [
            'genre_id' => $response->json('data.id'),
            'category_id' => $categories_id[2]
        ]);
    }

    public function testRollbackStore()
    {
        $controller = \Mockery::mock(GenreController::class)
            ->makePartial()->shouldAllowMockingProtectedMethods();
        $controller->shouldReceive('validate')->withAnyArgs()->andReturn(['name' => 'test']);
        $controller->shouldReceive('rulesStore')->withAnyArgs()->andReturn([]);
        $controller->shouldReceive('handleRelations')->once()->andThrow(new TestException());
        $request = \Mockery::mock(Request::class);
        $hasErrors = false;
        try {
            $controller->store($request);
        } catch (TestException $e) {
            $this->assertCount(1, Genre::all());
            $hasErrors = true;
        }
        $this->assertTrue($hasErrors);
    }

    public function testRollbackUpdate()
    {
        $controller = \Mockery::mock(GenreController::class)
            ->makePartial()->shouldAllowMockingProtectedMethods();
        $controller->shouldReceive('findOrFail')->withAnyArgs()->andReturn($this->genre);
        $controller->shouldReceive('validate')->withAnyArgs()->andReturn(['name' => 'test']);
        $controller->shouldReceive('rulesStore')->withAnyArgs()->andReturn([]);
        $controller->shouldReceive('handleRelations')->once()->andThrow(new TestException());
        $request = \Mockery::mock(Request::class);
        $hasErrors = false;
        try {
            $controller->store($request);
        } catch (TestException $e) {
            $this->assertCount(1, Genre::all());
            $hasErrors = true;
        }
        $this->assertTrue($hasErrors);
    }

    protected function routeStore()
    {
        return route('genres.store');
    }

    protected function routeUpdate()
    {
        return route('genres.update', ['genre' => $this->genre->id]);
    }

    protected function model()
    {
        return Genre::class;
    }

    protected function assertHasCategory($genreId, $categoryId)
    {
        $this->assertDatabaseHas('category_genre', [
            'genre_id' => $genreId, 'category_id' => $categoryId
        ]);
    }
}
