<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\CastMember;
use Tests\TestCase;

class CastMemberTest extends TestCase
{
    use DatabaseMigrations;

    public function testList()
    {
        factory(CastMember::class, 1)->create();

        $castMember = CastMember::All();
        $this->assertCount(1, $castMember);

        $keys = array_keys($castMember->first()->getAttributes());
        $this->assertEqualsCanonicalizing(
            [
                'created_at', 'deleted_at', 'id',
                'type', 'name', 'updated_at'
            ],
            $keys
        );
    }

    public function testCreate()
    {
        $castMember = CastMember::create([
            'name' => 'test',
            'type' => CastMember::TYPE_ACTOR
        ]);
        $castMember->refresh();

        $this->assertEquals('test', $castMember->name);
        $this->assertEquals(CastMember::TYPE_ACTOR, $castMember->type);
        $this->assertUuidV4($castMember->id);

    }

    public function testUpdate()
    {
        $castMember = factory(CastMember::class)->create([
            'type' => CastMember::TYPE_ACTOR
        ])->first();

        $data = [
            'name' => 'test2'
        ];

        $castMember->update($data);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $castMember->{$key});
        }
    }

    public function testRemove()
    {
        $castMembers = factory(CastMember::class, 5)->create();
        $castMembers[0]->delete();
        $total = CastMember::count();
        $this->assertEquals(4, $total);
        $this->assertNull(CastMember::find($castMembers[0]->id));
        $castMembers[0]->restore();
        $this->assertNotEmpty(CastMember::find($castMembers[0]->id));
    }
}
