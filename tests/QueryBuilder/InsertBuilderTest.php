<?php

namespace Emonkak\Orm\Tests\QueryBuilder;

use Emonkak\Orm\QueryBuilder\InsertBuilder;
use Emonkak\Orm\QueryBuilder\SelectBuilder;
use Emonkak\Orm\QueryBuilder\Sql;

class InsertBuilderBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testPrefix()
    {
        $query = (new InsertBuilder())
            ->prefix('INSERT IGNORE')
            ->into('t1', ['c1', 'c2'])
            ->values(['foo', 'bar'])
            ->build();
        $this->assertSame('INSERT IGNORE INTO t1 (c1, c2) VALUES (?, ?)', $query->getSql(), 'INSERT IGNORE');
        $this->assertSame(['foo', 'bar'], $query->getBindings());
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
        $this->assertSame('INSERT INTO t1 (c1, c2, c3) SELECT c1, c2, c3 FROM t1 WHERE (c1 = ?)', $query->getSql(), 'INSERT SELECT');
    }

    public function testValues()
    {
        $query = (new InsertBuilder())
            ->into('t1', ['c1', 'c2', 'c3'])
            ->values(['foo', 'bar', 'baz'])
            ->values(['hoge', 'huga', 'piyo'])
            ->build();
        $this->assertSame('INSERT INTO t1 (c1, c2, c3) VALUES (?, ?, ?), (?, ?, ?)', $query->getSql());
        $this->assertSame(['foo', 'bar', 'baz', 'hoge', 'huga', 'piyo'], $query->getBindings());

        $query = (new InsertBuilder())
            ->into('t1', ['c1', 'c2', 'c3'])
            ->values(
                ['foo', 'bar', 'baz'],
                ['hoge', 'huga', 'piyo']
            )
            ->build();
        $this->assertSame('INSERT INTO t1 (c1, c2, c3) VALUES (?, ?, ?), (?, ?, ?)', $query->getSql());
        $this->assertSame(['foo', 'bar', 'baz', 'hoge', 'huga', 'piyo'], $query->getBindings());
    }

    public function testOnDuplicateKeyUpdate()
    {
        $query = (new InsertBuilder())
            ->into('t1', ['c1', 'c2', 'c3'])
            ->values(['foo', 'bar', 'baz'])
            ->onDuplicateKeyUpdate(['c2' => new Sql('c2 + ?', [1])])
            ->build();
        $this->assertSame('INSERT INTO t1 (c1, c2, c3) VALUES (?, ?, ?) ON DUPLICATE KEY UPDATE c2 = c2 + ?', $query->getSql());
        $this->assertSame(['foo', 'bar', 'baz', 1], $query->getBindings());
    }
}
