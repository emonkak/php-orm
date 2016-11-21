<?php

namespace Emonkak\Orm\Tests;

use Emonkak\Orm\DeleteBuilder;
use Emonkak\Orm\Grammar\DefaultGrammar;
use Emonkak\Orm\SelectBuilder;

/**
 * @covers Emonkak\Orm\DeleteBuilder
 */
class DeleteBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testGrammar()
    {
        $builder = (new DeleteBuilder());
        $this->assertEquals(DefaultGrammar::getInstance(), $builder->getGrammar());
    }

    public function testPrefix()
    {
        $query = (new DeleteBuilder())
            ->prefix('DELETE IGNORE')
            ->from('t1')
            ->where('c1', '=', 'foo')
            ->where('c2', '=', 'bar')
            ->build();
        $this->assertEquals('DELETE IGNORE FROM t1 WHERE ((c1 = ?) AND (c2 = ?))', $query->getSql());
        $this->assertEquals(['foo', 'bar'], $query->getBindings());
    }

    public function testWhere()
    {
        $query = (new DeleteBuilder())
            ->from('t1')
            ->where('c1', '=', 'foo')
            ->where('c2', 'IN', ['piyo', 'poyo'])
            ->build();
        $this->assertEquals('DELETE FROM t1 WHERE ((c1 = ?) AND (c2 IN (?, ?)))', $query->getSql());
        $this->assertEquals(['foo', 'piyo', 'poyo'], $query->getBindings());
    }

    public function testOrWhere()
    {
        $query = (new DeleteBuilder())
            ->from('t1')
            ->where('c1', '=', 'foo')
            ->orWhere('c2', 'IN', ['piyo', 'poyo'])
            ->build();
        $this->assertEquals('DELETE FROM t1 WHERE ((c1 = ?) OR (c2 IN (?, ?)))', $query->getSql());
        $this->assertEquals(['foo', 'piyo', 'poyo'], $query->getBindings());
    }

    public function testGroupWhere()
    {
        $query = (new DeleteBuilder())
            ->from('t1')
            ->where('c1', '=', 'foo')
            ->groupWhere(function($builder) {
                return $builder
                    ->where('c2', '=', 'bar')
                    ->orWhere('c3', '=', 'baz');
            })
            ->build();
        $this->assertEquals('DELETE FROM t1 WHERE ((c1 = ?) AND ((c2 = ?) OR (c3 = ?)))', $query->getSql());
        $this->assertEquals(['foo', 'bar', 'baz'], $query->getBindings());

        $query = (new DeleteBuilder())
            ->from('t1')
            ->where('c1', '=', 'foo')
            ->groupWhere(function($builder) {
                return $builder;
            })
            ->build();
        $this->assertEquals('DELETE FROM t1 WHERE (c1 = ?)', $query->getSql());
        $this->assertEquals(['foo'], $query->getBindings());
    }

    public function testOrGroupWhere()
    {
        $query = (new DeleteBuilder())
            ->from('t1')
            ->where('c1', '=', 'foo')
            ->orGroupWhere(function($builder) {
                return $builder
                    ->where('c2', '=', 'bar')
                    ->where('c3', '=', 'baz');
            })
            ->build();
        $this->assertEquals('DELETE FROM t1 WHERE ((c1 = ?) OR ((c2 = ?) AND (c3 = ?)))', $query->getSql());
        $this->assertEquals(['foo', 'bar', 'baz'], $query->getBindings());

        $query = (new DeleteBuilder())
            ->from('t1')
            ->where('c1', '=', 'foo')
            ->orGroupWhere(function($builder) {
                return $builder;
            })
            ->build();
        $this->assertEquals('DELETE FROM t1 WHERE (c1 = ?)', $query->getSql());
        $this->assertEquals(['foo'], $query->getBindings());
    }
}
