<?php

namespace Tests\Unit\Rules;

use App\Rules\GenresHasCategoriesRule;
use Mockery;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class GenresHasCategoriesRuleUnitTest extends TestCase
{
    public function testCategoriesIdField()
    {
        $rule = new GenresHasCategoriesRule([1, 1, 2, 2]);
        $reflectionClass = new ReflectionClass(GenresHasCategoriesRule::class);
        $reflectionProperty = $reflectionClass->getProperty('categoriesId');
        $reflectionProperty->setAccessible(true);
        $categoriesId = $reflectionProperty->getValue($rule);
        $this->assertEqualsCanonicalizing([1, 2],$categoriesId);
    }

    public function testGenresIdValue()
    {
        $rule = $this->creatRuleMock([]);
        $rule->shouldReceive('getRows')->withAnyArgs()->andReturnNull();
        $rule->passes('', [1, 1, 2, 2]);
        $reflectionClass = new ReflectionClass(GenresHasCategoriesRule::class);
        $reflectionProperty = $reflectionClass->getProperty('genresId');
        $reflectionProperty->setAccessible(true);
        $genresId = $reflectionProperty->getValue($rule);
        $this->assertEqualsCanonicalizing([1, 2],$genresId);
    }

    public function testPassesReturnsFalseWhenCategoriesOrGenresIsArrayEmpty()
    {
        $rule = $this->creatRuleMock([1]);
        $this->assertFalse($rule->passes('', []));

        $rule2 = $this->creatRuleMock([]);
        $this->assertFalse($rule2->passes('', [1]));
    }

    public function testPassesReturnsFalseWhenGetRowsIsEmpty()
    {
        $rule = $this->creatRuleMock([]);
        $rule->shouldReceive('getRows')->withAnyArgs()->andReturn(collect());
        $this->assertFalse($rule->passes('', [1]));
    }

    public function testPassesReturnsFalseWhenHasCategoriesWithoutGenres()
    {
        $rule = $this->creatRuleMock([1, 2]);
        $rule->shouldReceive('getRows')->withAnyArgs()->andReturn(collect(['category_id' => 1]));
        $this->assertFalse($rule->passes('', [1]));
    }

    public function testPassesIsValid()
    {
        $rule = $this->creatRuleMock([1, 2]);
        $rule->shouldReceive('getRows')->withAnyArgs()->andReturn(collect([['category_id' => 1], ['category_id' => 2]]));
        $this->assertTrue($rule->passes('', [1]));
    }

    protected function creatRuleMock($categoriesId)
    {
        return Mockery::mock(GenresHasCategoriesRule::class, [$categoriesId])
            ->makePartial()->shouldAllowMockingProtectedMethods();
    }
}
