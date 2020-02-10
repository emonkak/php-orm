<?php

declare(strict_types=1);

namespace Emonkak\Orm\Tests;

use Emonkak\Orm\DeleteBuilder;
use Emonkak\Orm\Grammar\GrammarInterface;
use PHPUnit\Framework\TestCase;

/**
 * @covers Emonkak\Orm\DeleteBuilder
 */
class DeleteBuilderTest extends TestCase
{
    use QueryBuilderTestTrait;

    public function testGetGrammar()
    {
        $grammar = $this->createMock(GrammarInterface::class);
        $queryBuilder = new DeleteBuilder($grammar);
        $this->assertSame($grammar, $queryBuilder->getGrammar());
    }

    public function testGetters()
    {
        $queryBuilder = $this->getDeleteBuilder()
            ->from('t1')
            ->where('c1', '=', 123);
        $this->assertSame('DELETE', $queryBuilder->getPrefix());
        $this->assertSame('t1', $queryBuilder->getFrom());
        $this->assertQueryIs('(c1 = ?)', [123], $queryBuilder->getWhere());
    }

    public function testPrefix()
    {
        $query = $this->getDeleteBuilder()
            ->prefix('DELETE IGNORE')
            ->from('t1')
            ->where('c1', '=', 'foo')
            ->where('c2', '=', 'bar')
            ->build();
        $this->assertQueryIs(
            'DELETE IGNORE FROM t1 WHERE ((c1 = ?) AND (c2 = ?))',
            ['foo', 'bar'],
            $query
        );
    }

    public function testWhere()
    {
        $query = $this->getDeleteBuilder()
            ->from('t1')
            ->where('c1', '=', 'foo')
            ->where('c2', 'IN', ['piyo', 'poyo'])
            ->build();
        $this->assertQueryIs(
            'DELETE FROM t1 WHERE ((c1 = ?) AND (c2 IN (?, ?)))',
            ['foo', 'piyo', 'poyo'],
            $query
        );
    }

    public function testOrWhere()
    {
        $query = $this->getDeleteBuilder()
            ->from('t1')
            ->where('c1', '=', 'foo')
            ->orWhere('c2', 'IN', ['piyo', 'poyo'])
            ->build();
        $this->assertQueryIs(
            'DELETE FROM t1 WHERE ((c1 = ?) OR (c2 IN (?, ?)))',
            ['foo', 'piyo', 'poyo'],
            $query
        );
    }
}
