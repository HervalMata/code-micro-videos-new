<?php

namespace Test\Unit\Models;

use App\Models\Genre;
use App\Models\Traits\uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Tests\TestCase;

class GenreTest extends TestCase
{
    private $genre;

    protected function setUp() : void
    {
        parent::setUp();
        $this->genre = new Genre();
    }

    public function testFillable()
    {
        $fillable = ['name', 'is_active'];
        $this->assertEquals($fillable, $this->genre->getFillable());
    }

    public function testHasCorrectTraits()
    {
        $traits = [
            SoftDeletes::class,
            uuid::class
        ];
        $useTraits = array_keys(class_uses(Genre::class));
        $this->assertEquals($useTraits, $traits);
    }

    public function testCasts()
    {
        $casts = [
            'id' => 'string',
            'is_active' => 'boolean'
        ];
        $this->assertEquals($casts, $this->genre->getCasts());
    }

    public function testIncrementing()
    {
        $this->assertFalse($this->genre->incrementing);
    }

    public function testDates()
    {
        $dates = ['deleted_at', 'created_at', 'updated_at'];
        foreach ($dates as $date) {
            $this->assertContains($date, $this->genre->getDates());
        }
        $this->assertCount(count($dates), $this->genre->getDates());
    }
}
