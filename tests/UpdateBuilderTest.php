<?php

namespace Emonkak\Orm\Tests;

use Emonkak\Orm\Grammar\DefaultGrammar;
use Emonkak\Orm\SelectBuilder;
use Emonkak\Orm\Sql;
use Emonkak\Orm\UpdateBuilder;

/**
 * @covers Emonkak\Orm\UpdateBuilder
 */
class UpdateBuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testGrammar()
    {
        $builder = (new UpdateBuilder());
        $this->assertEquals(DefaultGrammar::getInstance(), $builder->getGrammar());
    }

    public function testPrefix()
    {
        $query = (new UpdateBuilder())
            ->prefix('UPDATE IGNORE')
            ->table('t1')
            ->set('c1', 'foo')
            ->set('c2', 'bar')
            ->build();
        $this->assertEquals('UPDATE IGNORE t1 SET c1 = ?, c2 = ?', $query->getSql());
        $this->assertEquals(['foo', 'bar'], $query->getBindings());
    }

    public function testSet()
    {
        $query = (new UpdateBuilder())
            ->table('t1')
            ->set('c1', new Sql('c1 + ?', [1]))
            ->set('c2', 100)
            ->build();
        $this->assertEquals('UPDATE t1 SET c1 = c1 + ?, c2 = ?', $query->getSql());
        $this->assertEquals([1, 100], $query->getBindings());

        $builder = (new SelectBuilder)->select('c1')->from('t2')->limit(1);
        $query = (new UpdateBuilder())
            ->table('t1')
            ->set('c1', $builder)
            ->set('c2', 100)
            ->build();
        $this->assertEquals('UPDATE t1 SET c1 = (SELECT c1 FROM t2 LIMIT ?), c2 = ?', $query->getSql());
        $this->assertEquals([1, 100], $query->getBindings());
    }

    public function testSetAll()
    {
        $query = (new UpdateBuilder())
            ->table('t1')
            ->setAll(['c1' => new Sql('c1 + ?', [1]), 'c2' => 100])
            ->build();
        $this->assertEquals('UPDATE t1 SET c1 = c1 + ?, c2 = ?', $query->getSql());
        $this->assertEquals([1, 100], $query->getBindings());

        $builder = (new SelectBuilder)->select('c1')->from('t2')->limit(1);
        $query = (new UpdateBuilder())
            ->table('t1')
            ->setAll(['c1' => $builder, 'c2' => 100])
            ->build();
        $this->assertEquals('UPDATE t1 SET c1 = (SELECT c1 FROM t2 LIMIT ?), c2 = ?', $query->getSql());
        $this->assertEquals([1, 100], $query->getBindings());
    }

    public function testWhere()
    {
        $query = (new UpdateBuilder())
            ->table('t1')
            ->set('c1', 123)
            ->where('c2', '=', 456)
            ->where('c3', '=', 789)
            ->build();
        $this->assertEquals('UPDATE t1 SET c1 = ? WHERE ((c2 = ?) AND (c3 = ?))', $query->getSql());
        $this->assertEquals([123, 456, 789], $query->getBindings());
    }

    public function testOrWhere()
    {
        $query = (new UpdateBuilder())
            ->table('t1')
            ->set('c1', 123)
            ->where('c2', '=', 456)
            ->orWhere('c3', '=', 789)
            ->build();
        $this->assertEquals('UPDATE t1 SET c1 = ? WHERE ((c2 = ?) OR (c3 = ?))', $query->getSql());
        $this->assertEquals([123, 456, 789], $query->getBindings());
    }

    public function testGroupWhere()
    {
        $query = (new UpdateBuilder())
            ->table('t1')
            ->set('c1', 12)
            ->where('c2', '=', 34)
            ->groupWhere(function($builder) {
                return $builder
                    ->where('c3', '=', 56)
                    ->orWhere('c4', '=', 78);
            })
            ->build();
        $this->assertEquals('UPDATE t1 SET c1 = ? WHERE ((c2 = ?) AND ((c3 = ?) OR (c4 = ?)))', $query->getSql());
        $this->assertEquals([12, 34, 56, 78], $query->getBindings());

        $query = (new UpdateBuilder())
            ->table('t1')
            ->set('c1', 12)
            ->where('c2', '=', 34)
            ->groupWhere(function($builder) {
                return $builder;
            })
            ->build();
        $this->assertEquals('UPDATE t1 SET c1 = ? WHERE (c2 = ?)', $query->getSql());
        $this->assertEquals([12, 34], $query->getBindings());
    }

    public function testOrGroupWhere()
    {
        $query = (new UpdateBuilder())
            ->table('t1')
            ->set('c1', 12)
            ->where('c2', '=', 34)
            ->orGroupWhere(function($builder) {
                return $builder
                    ->where('c3', '=', 56)
                    ->where('c4', '=', 78);
            })
            ->build();
        $this->assertEquals('UPDATE t1 SET c1 = ? WHERE ((c2 = ?) OR ((c3 = ?) AND (c4 = ?)))', $query->getSql());
        $this->assertEquals([12, 34, 56, 78], $query->getBindings());

        $query = (new UpdateBuilder())
            ->table('t1')
            ->set('c1', 12)
            ->where('c2', '=', 34)
            ->orGroupWhere(function($builder) {
                return $builder;
            })
            ->build();
        $this->assertEquals('UPDATE t1 SET c1 = ? WHERE (c2 = ?)', $query->getSql());
        $this->assertEquals([12, 34], $query->getBindings());
    }
}
