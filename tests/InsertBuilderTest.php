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
        $builder = new InsertBuilder($grammar);
        $this->assertSame($grammar, $builder->getGrammar());
    }

    public function testPrefix()
    {
        $query = $this->createInsertBuilder()
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
        $builder = $this->createSelectBuilder()
            ->select('c1')
            ->select('c2')
            ->select('c3')
            ->from('t1')
            ->where('c1', '=', 'foo');
        $query = $this->createInsertBuilder()
            ->into('t1', ['c1', 'c2', 'c3'])
            ->select($builder->build())
            ->build();
        $this->assertQueryIs(
            'INSERT INTO t1 (c1, c2, c3) SELECT c1, c2, c3 FROM t1 WHERE (c1 = ?)',
            ['foo'],
            $query
        );
    }

    public function testValues()
    {
        $query = $this->createInsertBuilder()
            ->into('t1', ['c1', 'c2', 'c3'])
            ->values(['foo', 'bar', 'baz'])
            ->values(['hoge', 'huga', 'piyo'])
            ->build();
        $this->assertQueryIs(
            'INSERT INTO t1 (c1, c2, c3) VALUES (?, ?, ?), (?, ?, ?)',
            ['foo', 'bar', 'baz', 'hoge', 'huga', 'piyo'],
            $query
        );

        $query = $this->createInsertBuilder()
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
