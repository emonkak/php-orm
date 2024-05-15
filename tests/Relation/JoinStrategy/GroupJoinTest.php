<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests\Relation;

use Emonkak\Enumerable\LooseEqualityComparer;
use Emonkak\Orm\Relation\JoinStrategy\GroupJoin;
use PHPUnit\Framework\TestCase;

/**
 * @covers \Emonkak\Orm\Relation\JoinStrategy\GroupJoin
 */
class GroupJoinTest extends TestCase
{
    public function testJoin(): void
    {
        $talents = [
            ['talent_id' => 1, 'name' => 'Sumire Uesaka'],
            ['talent_id' => 2, 'name' => 'Mikako Komatsu'],
            ['talent_id' => 3, 'name' => 'Rumi Okubo'],
            ['talent_id' => 4, 'name' => 'Natsumi Takamori'],
            ['talent_id' => 5, 'name' => 'Shiori Mikami'],
        ];
        $programs = [
            ['program_id' => 1, 'talent_id' => '1'],
            ['program_id' => 2, 'talent_id' => '1'],
            ['program_id' => 3, 'talent_id' => '2'],
            ['program_id' => 4, 'talent_id' => '2'],
            ['program_id' => 5, 'talent_id' => '4'],
            ['program_id' => 6, 'talent_id' => '5'],
        ];
        $expectedResult = [
            $talents[0] + ['programs' => [$programs[0], $programs[1]]],
            $talents[1] + ['programs' => [$programs[2], $programs[3]]],
            $talents[2] + ['programs' => []],
            $talents[3] + ['programs' => [$programs[4]]],
            $talents[4] + ['programs' => [$programs[5]]],
        ];

        // Test whether IDs of different types can be joined.
        $outerKeySelector = function(array $talent): int { return $talent['talent_id']; };
        $innerKeySelector = function(array $program): string { return $program['talent_id']; };
        $resultSelector = function(array $talent, array $programs): array {
            $talent['programs'] = $programs;
            return $talent;
        };
        /** @var LooseEqualityComparer<mixed> */
        $comparer = LooseEqualityComparer::getInstance();
        $groupJoin = new GroupJoin(
            $outerKeySelector,
            $innerKeySelector,
            $resultSelector,
            $comparer
        );

        $this->assertSame($outerKeySelector, $groupJoin->getOuterKeySelector());
        $this->assertSame($innerKeySelector, $groupJoin->getInnerKeySelector());
        $this->assertSame($resultSelector, $groupJoin->getResultSelector());
        $this->assertSame($comparer, $groupJoin->getComparer());

        $result = $groupJoin->join(
            $talents,
            $programs
        );
        $result = iterator_to_array($result);
        $this->assertEquals($expectedResult, $result);
    }
}
