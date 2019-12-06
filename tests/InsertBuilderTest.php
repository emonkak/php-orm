<?php

namespace Emonkak\Orm\Tests;

use Emonkak\Orm\Grammar\GrammarInterface;
use Emonkak\Orm\InsertBuilder;
use Emonkak\Orm\Sql;

/**
 * @covers Emonkak\Orm\InsertBuilder
 */
class InsertBuilderTest extends \PHPUnit_Framework_TestCase
{
    use QueryBuilderTestTrait;

    public function testGetGrammar()
    {
        $grammar = $this->createMock(GrammarInterface::class);
        $queryBuilder = new InsertBuilder($grammar);
        $this->assertSame($grammar, $queryBuilder->getGrammar());
    }

    public function testGetters()
    {
        $queryBuilder = $this->getInsertBuilder()
            ->into('t1', ['c1', 'c2', 'c3'])
            ->values(['foo', 'bar', 'baz']);
        $this->assertSame('INSERT', $queryBuilder->getPrefix());
        $this->assertSame('t1', $queryBuilder->getInto());
        $this->assertEquals(['c1', 'c2', 'c3'], $queryBuilder->getColumns());
        $this->assertEquals([
            [
                new Sql('?', ['foo']),
                new Sql('?', ['bar']),
                new Sql('?', ['baz'])
            ]
        ], $queryBuilder->getValues());

        $selectQuery = $this->getSelectBuilder()
            ->select('c1')
            ->from('t1')
            ->build();
        $queryBuilder = $this->getInsertBuilder()
            ->into('t1', ['c1'])
            ->select($selectQuery);
        $this->assertSame('INSERT', $queryBuilder->getPrefix());
        $this->assertSame('t1', $queryBuilder->getInto());
        $this->assertEquals(['c1'], $queryBuilder->getColumns());
        $this->assertEquals($selectQuery, $queryBuilder->getSelect());
    }

    public function testPrefix()
    {
        $query = $this->getInsertBuilder()
            ->prefix('INSERT IGNORE')
            ->into('t1', ['c1', 'c2'])
            ->values(['foo', 'bar'])
            ->build();
        $this->assertQueryIs(
            'INSERT IGNORE INTO t1 (c1, c2) VALUES (?, ?)',
            ['foo', 'bar'],
            $query
        );
    }

    public function testSelect()
    {
        $selectQuery = $this->getSelectBuilder()
            ->select('c1')
            ->select('c2')
            ->select('c3')
            ->from('t1')
            ->where('c1', '=', 'foo')
            ->build();
        $query = $this->getInsertBuilder()
            ->into('t1', ['c1', 'c2', 'c3'])
            ->select($selectQuery)
            ->build();
        $this->assertQueryIs(
            'INSERT INTO t1 (c1, c2, c3) SELECT c1, c2, c3 FROM t1 WHERE (c1 = ?)',
            ['foo'],
            $query
        );
    }

    public function testValues()
    {
        $query = $this->getInsertBuilder()
            ->into('t1', ['c1', 'c2', 'c3'])
            ->values(['foo', 'bar', 'baz'])
            ->values(['hoge', 'huga', 'piyo'])
            ->build();
        $this->assertQueryIs(
            'INSERT INTO t1 (c1, c2, c3) VALUES (?, ?, ?), (?, ?, ?)',
            ['foo', 'bar', 'baz', 'hoge', 'huga', 'piyo'],
            $query
        );

        $query = $this->getInsertBuilder()
            ->into('t1', ['c1', 'c2', 'c3'])
            ->values(
                ['foo', 'bar', 'baz'],
                ['hoge', 'huga', 'piyo']
            )
            ->build();
        $this->assertQueryIs(
            'INSERT INTO t1 (c1, c2, c3) VALUES (?, ?, ?), (?, ?, ?)',
            ['foo', 'bar', 'baz', 'hoge', 'huga', 'piyo'],
            $query
        );
    }
}
