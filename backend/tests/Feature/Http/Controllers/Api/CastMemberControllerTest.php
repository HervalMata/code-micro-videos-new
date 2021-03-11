<?php

namespace Tests\Feature\Http\Controllers\Api;

use App\Models\CastMember;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;
use Tests\Traits\TestResources;
use Tests\Traits\TestSaves;
use Tests\Traits\TestValidations;

class CastMemberControllerTest extends TestCase
{
    use DatabaseMigrations;
    use TestValidations;
    use TestSaves;
    use TestResources;

    private $castMember;

    private $serializedFields = [
        'id', 'name', 'type', 'created_at', 'updated_at', 'deleted_at'
    ];

    protected function setUp() : void
    {
        parent::setUp();
        $this->castMember = factory(CastMember::class)->create([
            'type' => CastMember::TYPE_DIRECTOR
        ]);
    }

    public function testIndex()
    {
        $response = $this->get(route('cast_members.index'));

        $response->assertStatus(200)
               ->assertJson([$this->castMember->toArray()])->assertJsonStructure([
                'data' => [
                    '*' => $this->serializedFields
                ]
            ]);
    }

    public function testShow()
    {
        $response = $this->get(route('cast_members.show', ['cast_member' => $this->castMember->id]));

        $response->assertStatus(200)
            ->assertJsonStructure([
                'data' => $this->serializedFields
            ])
               ->assertJson($this->castMember->toArray());
    }

    public function testInvalidationData()
    {
        $data = ['name' => '', 'type' => ''];
        $this->assertInvalidationStoreAction($data, 'required');
        $this->assertInvalidationUpdateAction($data, 'required');

        $data = ['name' => str_repeat('a', 256)];
        $this->assertInvalidationStoreAction($data,'max.string', ['max' => 255]);
        $this->assertInvalidationUpdateAction($data, 'max.string', ['max' => 255]);

        $data = ['type' => 'a'];
        $this->assertInvalidationStoreAction($data, 'in');
        $this->assertInvalidationUpdateAction($data, 'in');
    }

    /**
     * @throws Exception
     */
    public function testStore()
    {
        $data = [
            ['name' => 'test', 'type' => CastMember::TYPE_ACTOR],
            ['name' => 'test', 'type' => CastMember::TYPE_DIRECTOR]
        ];

        foreach ($data as $key => $value) {
            $response = $this->assertStore($value, $value + ['deleted_at' => null]);
            $response->assertJsonStructure(
                [
                    'data' => $this->serializedFields
                ]
            );
        }

    }

    /**
     * @throws \Exception
     */
    public function testUpdate()
    {

        $data = [
            'name' => 'test', 'type' => CastMember::TYPE_ACTOR
        ];

        $response = $this->assertUpdate($data, $data + ['deleted_at' => null]);
        $response->assertJsonStructure(
            [
                'data' => $this->serializedFields
            ]
        );
    }

    public function testRemove()
    {
        $response = $this->json('DELETE', route('cast_members.destroy', ['cast_member' => $this->castMember->id]));

        $response->assertStatus(204);

        $this->assertNull(CastMember::find($this->castMember->id));
    }

    protected function routeStore()
    {
        return route('cast_members.store');
    }

    protected function routeUpdate()
    {
        return route('cast_members.update', ['cast_member' => $this->castMember->id]);
    }

    protected function model()
    {
        return CastMember::class;
    }
}
