<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\Video;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class VideoControllerTest extends TestCase
{
    use DatabaseMigrations;
    use TestValidations;
    use TestSaves;

    private $video;
    private $sendData;

    protected function setUp(): void
    {
        parent::setUp();
        $this->sendData = [
            'title' => 'some title',
            'description' => 'short description',
            'year_launched' => 1983,
            'rating' => Video::RATING_LIST[0],
            'duration' => 30,
        ];
        $this->video = factory(Video::class)->create();
    }

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
            'rating' => '', 'duration' => '',
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

    public function testRemove()
    {
        $video = factory(Video::class)->create();
        $response = $this->json('DELETE', route('videos.destroy', ['video' => $video->id]));

        $response->assertStatus(204);

        $this->assertNull(Video::find($video->id));
    }

    public function testStore()
    {
        $data = [
            [
                'send_data' => $this->sendData,
                'test_data' => $this->sendData + ['opened' => false],
            ],
            [
                'send_data' => $this->sendData + ['opened' => true],
                'test_data' => $this->sendData + ['opened' => true],
            ],
            [
                'send_data' => $this->sendData + ['rating' => Video::RATING_LIST[1]],
                'test_data' => $this->sendData + ['rating' => Video::RATING_LIST[1]],
            ]
        ];

        foreach ($data as $key => $value) {
            $response = $this->assertStore($value['send_data'], $value['test_data'] + ['deleted_at' => null]);
            $response->assertJsonStructure(['created_at', 'updated_at']);

            $response = $this->assertUpdate($value['send_data'], $value['test_data'] + ['deleted_at' => null]);
            $response->assertJsonStructure(['created_at', 'updated_at']);
        }
    }

    /*public function testUpdate()
    {
        $this->video = factory(Video::class)->create([
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

    }*/



    protected function routeStore()
    {
        return route('videos.store');
    }

    protected function routeUpdate()
    {
        return route('videos.update', ['video' => $this->video->id]);
    }

    protected function model()
    {
        return Video::class;
    }
}
