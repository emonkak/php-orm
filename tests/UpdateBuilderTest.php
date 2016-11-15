<?php

namespace Emonkak\Orm\Tests;

use Emonkak\Orm\SelectBuilder;
use Emonkak\Orm\Sql;
use Emonkak\Orm\UpdateBuilder;

class UpdateBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testPrefix()
    {
        $query = (new UpdateBuilder())
            ->prefix('UPDATE IGNORE')
            ->table('t1')
            ->set('c1', 'foo')
            ->set('c2', 'bar')
            ->build();
        $this->assertSame('UPDATE IGNORE t1 SET c1 = ?, c2 = ?', $query->getSql());
        $this->assertSame(['foo', 'bar'], $query->getBindings());
    }

    public function testTable()
    {
        $query = (new UpdateBuilder())
            ->table('t1', 'a1')
            ->set('a1.c1', 'foo')
            ->set('a1.c2', 'bar')
            ->build();
        $this->assertSame('UPDATE t1 AS a1 SET a1.c1 = ?, a1.c2 = ?', $query->getSql());
        $this->assertSame(['foo', 'bar'], $query->getBindings());
    }

    public function testSet()
    {
        $query = (new UpdateBuilder())
            ->table('t1')
            ->set('c1', new Sql('c1 + ?', [1]))
            ->set('c2', 100)
            ->build();
        $this->assertSame('UPDATE t1 SET c1 = c1 + ?, c2 = ?', $query->getSql());
        $this->assertSame([1, 100], $query->getBindings());

        $builder = (new SelectBuilder)->select('c1')->from('t2')->limit(1);
        $query = (new UpdateBuilder())
            ->table('t1')
            ->set('c1', $builder)
            ->set('c2', 100)
            ->build();
        $this->assertSame('UPDATE t1 SET c1 = (SELECT c1 FROM t2 LIMIT ?), c2 = ?', $query->getSql(), 'サブクエリ');
        $this->assertSame([1, 100], $query->getBindings());
    }

    public function testSetAll()
    {
        $query = (new UpdateBuilder())
            ->table('t1')
            ->setAll(['c1' => new Sql('c1 + ?', [1]), 'c2' => 100])
            ->build();
        $this->assertSame('UPDATE t1 SET c1 = c1 + ?, c2 = ?', $query->getSql());
        $this->assertSame([1, 100], $query->getBindings());

        $builder = (new SelectBuilder)->select('c1')->from('t2')->limit(1);
        $query = (new UpdateBuilder())
            ->table('t1')
            ->setAll(['c1' => $builder, 'c2' => 100])
            ->build();
        $this->assertSame('UPDATE t1 SET c1 = (SELECT c1 FROM t2 LIMIT ?), c2 = ?', $query->getSql(), 'サブクエリ');
        $this->assertSame([1, 100], $query->getBindings());
    }

    public function testWhere()
    {
        $query = (new UpdateBuilder())
            ->table('t1')
            ->set('c1', 100)
            ->where('c1', '>', 100)
            ->build();
        $this->assertSame('UPDATE t1 SET c1 = ? WHERE (c1 > ?)', $query->getSql(), '大なり');
        $this->assertSame([100, 100], $query->getBindings());

        $query = (new UpdateBuilder())
            ->table('t1')
            ->set('c1', 100)
            ->where('c2', 'IN', [1, 2, 3])
            ->build();
        $this->assertSame('UPDATE t1 SET c1 = ? WHERE (c2 IN (?, ?, ?))', $query->getSql(), 'IN句');
        $this->assertSame([100, 1, 2, 3], $query->getBindings());
    }

    public function testOrderBy()
    {
        $query = (new UpdateBuilder())
            ->table('t1')
            ->set('c1', 100)
            ->where('c1', '>', 100)
            ->orderBy('c1')
            ->build();
        $this->assertSame('UPDATE t1 SET c1 = ? WHERE (c1 > ?) ORDER BY c1', $query->getSql());
        $this->assertSame([100, 100], $query->getBindings());

        $query = (new UpdateBuilder())
            ->table('t1')
            ->set('c1', 100)
            ->where('c1', '>', 100)
            ->orderBy('c1')
            ->orderBy('c2')
            ->build();
        $this->assertSame('UPDATE t1 SET c1 = ? WHERE (c1 > ?) ORDER BY c1, c2', $query->getSql());
        $this->assertSame([100, 100], $query->getBindings());
    }

    public function testLimit()
    {
        $query = (new UpdateBuilder())
            ->table('t1')
            ->set('c1', 100)
            ->orderBy('c1', 'DESC')
            ->limit(10)
            ->build();
        $this->assertSame('UPDATE t1 SET c1 = ? ORDER BY c1 DESC LIMIT ?', $query->getSql());
        $this->assertSame([100, 10], $query->getBindings());
    }
}
