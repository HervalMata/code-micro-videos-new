<?php

namespace Tests\Feature\Http\Controllers\Api\VideoController;

use App\Models\Category;
use App\Models\Genre;
use App\Models\Video;
use Tests\Feature\Http\Controllers\Api\VideoController\BaseVideoControllerTestCase;
use Tests\Traits\TestSaves;

class VideoControllerCrudTest extends BaseVideoControllerTestCase
{
    use TestSaves;

    public function testIndex()
    {
        $response = $this->get(route('videos.index'));

        $response->assertStatus(200)
            ->assertJson([$this->video->toArray()]);
    }

    public function testShow()
    {
        $response = $this->get(route('videos.show', ['video' => $this->video->id]));

        $response->assertStatus(200)
            ->assertJson($this->video->toArray());
    }

    public function testInvalidationRequired()
    {
        $data = [
            'title' => '', 'description' => '', 'year_launched' => '',
            'rating' => '', 'duration' => '', 'categories_id' => '',
            'genres_id' => ''
        ];

        $this->assertInvalidationStoreAction($data, 'required');
        $this->assertInvalidationUpdateAction($data, 'required');
    }

    public function testInvalidationMax()
    {
        $data = ['title' => str_repeat('a', 256)];
        $this->assertInvalidationStoreAction($data, 'max.string', ['max' => 255]);
        $this->assertInvalidationUpdateAction($data, 'max.string', ['max' => 255]);
    }

    public function testInvalidationInteger()
    {
        $data = ['duration' => 's'];
        $this->assertInvalidationStoreAction($data, 'integer');
        $this->assertInvalidationUpdateAction($data, 'integer');

    }

    public function testInvalidationYearLaunched()
    {
        $data = ['year_launched' => 'a'];
        $this->assertInvalidationStoreAction($data, 'date_format', ['format' => 'Y']);
        $this->assertInvalidationUpdateAction($data, 'date_format', ['format' => 'Y']);
    }

    public function testInvalidationOpéned()
    {
        $data = ['opened' => 't'];
        $this->assertInvalidationStoreAction($data, 'boolean');
        $this->assertInvalidationUpdateAction($data, 'boolean');
    }

    public function testInvalidationRating()
    {
        $data = ['rating' => 'PP'];
        $this->assertInvalidationStoreAction($data, 'in');
        $this->assertInvalidationUpdateAction($data, 'in');
    }

    public function testInvalidationCategoriesId()
    {
        $data = ['categories_id' => 'a'];
        $this->assertInvalidationStoreAction($data, 'array');
        $this->assertInvalidationUpdateAction($data, 'array');

        $data = ['categories_id' => [999]];
        $this->assertInvalidationStoreAction($data, 'exists');
        $this->assertInvalidationUpdateAction($data, 'exists');

        $category = factory(Category::class)->create();
        $category->delete();

        $data = ['categories_id' => [$category->id]];
        $this->assertInvalidationStoreAction($data, 'exists');
        $this->assertInvalidationUpdateAction($data, 'exists');
    }

    public function testInvalidationGenresId()
    {
        $data = ['genres_id' => 'a'];
        $this->assertInvalidationStoreAction($data, 'array');
        $this->assertInvalidationUpdateAction($data, 'array');

        $data = ['genres_id' => [999]];
        $this->assertInvalidationStoreAction($data, 'exists');
        $this->assertInvalidationUpdateAction($data, 'exists');

        $genre = factory(Genre::class)->create();
        $genre->delete();

        $data = ['genres_id' => [$genre->id]];
        $this->assertInvalidationStoreAction($data, 'exists');
        $this->assertInvalidationUpdateAction($data, 'exists');
    }

    public function testRemove()
    {
        $video = factory(Video::class)->create();
        $response = $this->json('DELETE', route('videos.destroy', ['video' => $video->id]));

        $response->assertStatus(204);

        $this->assertNull(Video::find($video->id));
    }

    public function testStore()
    {
        $categories = factory(Category::class, 3)->create();
        $genres = factory(Genre::class, 2)->create();
        $genres[0]->categories()->sync($categories->pluck('id')->toArray());
        $genres[1]->categories()->sync($categories->pluck('id')->toArray());

        $extra = [
            'categories_id' => $categories->pluck('id')->toArray(),
            'genres_id' => $genres->pluck('id')->toArray()
        ];

        $data = [
            [
                'send_data' => $this->sendData + $extra,
                'test_data' => $this->sendData
            ],
            [
                'send_data' => $this->sendData + ['opened' => true] + $extra,
                'test_data' => $this->sendData + ['opened' => true],
            ],
            [
                'send_data' => $this->sendData + ['rating' => Video::RATING_LIST[1]] + $extra,
                'test_data' => $this->sendData + ['rating' => Video::RATING_LIST[1]],
            ]
        ];

        foreach ($data as $key => $value) {
            $response = $this->assertStore($value['send_data'], $value['test_data'] + ['deleted_at' => null]);
            $response->assertJsonStructure(['created_at', 'updated_at']);
            $video = Video::find($response->json('id'));
            $video->load('categories');
            $video->load('genres');
            $this->assertEqualsCanonicalizing($video->categories->pluck('id')->toArray(), $extra['categories_id']);
            $this->assertEqualsCanonicalizing($video->genres->pluck('id')->toArray(), $extra['genres_id']);

            $video = Video::find($this->video->id);
            $response = $this->assertUpdate($value['send_data'], $value['test_data'] + ['deleted_at' => null]);
            $response->assertJsonStructure(['created_at', 'updated_at']);
            $video->load('categories');
            $video->load('genres');
            $this->assertEqualsCanonicalizing($video->categories->pluck('id')->toArray(), $extra['categories_id']);
            $this->assertEqualsCanonicalizing($video->genres->pluck('id')->toArray(), $extra['genres_id']);
        }
    }

    public function testSyncCategories()
    {
        $categories_id = factory(Category::class, 3)->create()->pluck('id')->toArray();
        $genre = factory(Genre::class)->create();
        $genre->categories()->sync($categories_id);
        $extra = [
            'genres_id' => [$genre->id],
            'categories_id' => [$categories_id[0]]
        ];
        $response = $this->json('POST', $this->routeStore(), $this->sendData + $extra);
        $this->assertDatabaseHas('category_video', [
            'video_id' => $response->json('id'),
            'category_id' => $categories_id[0]
        ]);
        $extra = [
            'genres_id' => [$genre->id],
            'categories_id' => [$categories_id[1], $categories_id[2]]
        ];
        $response = $this->json('PUT', route('videos.update', ['video' => $response->json('id')]), $this->sendData + $extra);
        $this->assertDatabaseMissing('category_video', [
            'video_id' => $response->json('id'),
            'category_id' => $categories_id[0]
        ]);
        $this->assertDatabaseHas('category_video', [
            'video_id' => $response->json('id'),
            'category_id' => $categories_id[1]
        ]);
        $this->assertDatabaseHas('category_video', [
            'video_id' => $response->json('id'),
            'category_id' => $categories_id[2]
        ]);
    }

    public function testSyncGenres()
    {
        $genres = factory(Genre::class, 3)->create();
        $genres_id = $genres->pluck('id')->toArray();
        $category_id = factory(Category::class)->create()->id;
        $genres->each(function ($genre) use ($category_id) {
            $genre->categories()->sync($category_id);
        });

        $extra = [
            'genres_id' => [$genres_id[0]],
            'categories_id' => [$category_id]
        ];
        $response = $this->json('POST', $this->routeStore(), $this->sendData + $extra);
        $this->assertDatabaseHas('genre_video', [
            'video_id' => $response->json('id'),
            'genre_id' => $genres_id[0]
        ]);
        $extra = [
            'categories_id' => [$category_id],
            'genres_id' => [$genres_id[1], $genres_id[2]]
        ];
        $response = $this->json('PUT', route('videos.update', ['video' => $response->json('id')]), $this->sendData + $extra);
        $this->assertDatabaseMissing('genre_video', [
            'video_id' => $response->json('id'),
            'genre_id' => $genres_id[0]
        ]);
        $this->assertDatabaseHas('genre_video', [
            'video_id' => $response->json('id'),
            'genre_id' => $genres_id[1]
        ]);
        $this->assertDatabaseHas('genre_video', [
            'video_id' => $response->json('id'),
            'genre_id' => $genres_id[2]
        ]);
    }
}
