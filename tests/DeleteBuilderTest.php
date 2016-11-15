<?php

namespace Emonkak\Orm\Tests;

use Emonkak\Orm\DeleteBuilder;
use Emonkak\Orm\SelectBuilder;

class DeleteBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testPrefix()
    {
        $query = (new DeleteBuilder())
            ->prefix('DELETE IGNORE')
            ->from('t1')
            ->where('c1', '=', 'foo')
            ->where('c2', '=', 'bar')
            ->build();
        $this->assertSame('DELETE IGNORE FROM t1 WHERE ((c1 = ?) AND (c2 = ?))', $query->getSql());
        $this->assertSame(['foo', 'bar'], $query->getBindings());
    }

    public function testWhere()
    {
        $query = (new DeleteBuilder())
            ->from('t1')
            ->where('c1', '=', 'foo')
            ->where('c2', 'IN', ['piyo', 'poyo'])
            ->build();
        $this->assertSame('DELETE FROM t1 WHERE ((c1 = ?) AND (c2 IN (?, ?)))', $query->getSql());
        $this->assertSame(['foo', 'piyo', 'poyo'], $query->getBindings());
    }

    public function testOrderBy()
    {
        $query = (new DeleteBuilder())
            ->from('t1')
            ->where('c1', '=', 'foo')
            ->orderBy('c1', 'DESC')
            ->build();
        $this->assertSame('DELETE FROM t1 WHERE (c1 = ?) ORDER BY c1 DESC', $query->getSql());
        $this->assertSame(['foo'], $query->getBindings());

        $query = (new DeleteBuilder())
            ->from('t1')
            ->where('c1', '=', 'foo')
            ->orderBy('c1')
            ->orderBy('c2')
            ->build();
        $this->assertSame('DELETE FROM t1 WHERE (c1 = ?) ORDER BY c1, c2', $query->getSql(), '');
        $this->assertSame(['foo'], $query->getBindings());
    }

    public function testLimit()
    {
        $query = (new DeleteBuilder())
            ->from('t1')
            ->where('c1', '=', 'foo')
            ->orderBy('c1')
            ->limit(10)
            ->build();
        $this->assertSame('DELETE FROM t1 WHERE (c1 = ?) ORDER BY c1 LIMIT ?', $query->getSql());
        $this->assertSame(['foo', 10], $query->getBindings());
    }
}
