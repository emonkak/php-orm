<?php

namespace Emonkak\Orm\Tests;

use Emonkak\Orm\Grammar\DefaultGrammar;
use Emonkak\Orm\InsertBuilder;
use Emonkak\Orm\SelectBuilder;
use Emonkak\Orm\Sql;

/**
 * @covers Emonkak\Orm\InsertBuilder
 */
class InsertBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testGrammar()
    {
        $builder = (new InsertBuilder());
        $this->assertEquals(DefaultGrammar::getInstance(), $builder->getGrammar());
    }

    public function testPrefix()
    {
        $query = (new InsertBuilder())
            ->prefix('INSERT IGNORE')
            ->into('t1', ['c1', 'c2'])
            ->values(['foo', 'bar'])
            ->build();
        $this->assertEquals('INSERT IGNORE INTO t1 (c1, c2) VALUES (?, ?)', $query->getSql());
        $this->assertEquals(['foo', 'bar'], $query->getBindings());
    }

    public function testSelect()
    {
        $builder = (new SelectBuilder)
            ->select('c1')
            ->select('c2')
            ->select('c3')
            ->from('t1')
            ->where('c1', '=', 'foo');
        $query = (new InsertBuilder())
            ->into('t1', ['c1', 'c2', 'c3'])
            ->select($builder->build())
            ->build();
        $this->assertEquals('INSERT INTO t1 (c1, c2, c3) SELECT c1, c2, c3 FROM t1 WHERE (c1 = ?)', $query->getSql());
    }

    public function testValues()
    {
        $query = (new InsertBuilder())
            ->into('t1', ['c1', 'c2', 'c3'])
            ->values(['foo', 'bar', 'baz'])
            ->values(['hoge', 'huga', 'piyo'])
            ->build();
        $this->assertEquals('INSERT INTO t1 (c1, c2, c3) VALUES (?, ?, ?), (?, ?, ?)', $query->getSql());
        $this->assertEquals(['foo', 'bar', 'baz', 'hoge', 'huga', 'piyo'], $query->getBindings());

        $query = (new InsertBuilder())
            ->into('t1', ['c1', 'c2', 'c3'])
            ->values(
                ['foo', 'bar', 'baz'],
                ['hoge', 'huga', 'piyo']
            )
            ->build();
        $this->assertEquals('INSERT INTO t1 (c1, c2, c3) VALUES (?, ?, ?), (?, ?, ?)', $query->getSql());
        $this->assertEquals(['foo', 'bar', 'baz', 'hoge', 'huga', 'piyo'], $query->getBindings());
    }
}
