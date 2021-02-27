<?php

namespace Tests\Unit\Models;

use App\Models\CastMember;
use App\Models\Traits\uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use PHPUnit\Framework\TestCase;

class CastMemberTest extends TestCase
{
    private $castMember;

    protected function setUp(): void
    {
        parent::setUp();
        $this->castMember = new CastMember();
    }

    public function testFillable()
    {
        $fillable = ['name', 'type'];
        $this->assertEquals($fillable, $this->castMember->getFillable());
    }

    public function testHasCorrectTraits()
    {
        $traits = [
            SoftDeletes::class,
            uuid::class
        ];
        $useTraits = array_keys(class_uses(CastMember::class));
        $this->assertEquals($useTraits, $traits);
    }

    public function testCasts()
    {
        $casts = [
            'id' => 'string'
        ];
        $this->assertEquals($casts, $this->castMember->getCasts());
    }

    public function testIncrementing()
    {
        $this->assertFalse($this->castMember->incrementing);
    }

    public function testDates()
    {
        $dates = ['deleted_at', 'created_at', 'updated_at'];
        foreach ($dates as $date) {
            $this->assertContains($date, $this->castMember->getDates());
        }
        $this->assertCount(count($dates), $this->castMember->getDates());
    }
}
