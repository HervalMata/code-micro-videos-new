<?php

namespace Tests\Feature\Models;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use App\Models\Genre;

class GenreTest extends TestCase
{
    use DatabaseMigrations;

    public function testList()
    {
        factory(Genre::class, 1)->create();

        $genres = Genre::All();
        $this->assertCount(1, $genres);

        $keys = array_keys($genres->first()->getAttributes());
        $this->assertEqualsCanonicalizing(
            [
                'created_at', 'deleted_at', 'id',
                'is_active', 'name', 'updated_at'
            ], $keys
        );
    }

    public function testCreate()
    {
        $genre = Genre::create(['name' => 'test']);
        $genre->refresh();

        $this->assertEquals('test', $genre->name);
        $this->assertTrue($genre->is_active);
        $this->assertUuidV4($genre->id);

        $genre = Genre::create([
            'name' => 'test',
            'is_active' => false
        ]);

        $this->assertFalse($genre->is_active);

        $genre = Genre::create([
            'name' => 'test',
            'is_active' => true
        ]);

        $this->assertTrue($genre->is_active);
    }

    public function testUpdate()
    {
        $genre = factory(Genre::class)->create([
            'is_active' => false
        ])->first();

        $data = [
            'name' => 'test2',
            'is_active' => true
        ];

        $genre->update($data);

        foreach ($data as $key => $value) {
            $this->assertEquals($value, $genre->{$key});
        }
    }

    public function testRemove()
    {
        $genres = factory(Genre::class, 2)->create();
        $genres[0]->delete();
        $total = Genre::count();
        $this->assertEquals(1, $total);
    }
}
