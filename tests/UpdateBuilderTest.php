<?php

namespace Emonkak\Orm\Tests;

use Emonkak\Orm\Grammar\GrammarInterface;
use Emonkak\Orm\Sql;
use Emonkak\Orm\UpdateBuilder;

/**
 * @covers Emonkak\Orm\UpdateBuilder
 */
class UpdateBuilderTest extends \PHPUnit_Framework_TestCase
{
    use QueryBuilderTestTrait;

    public function testGetGrammar()
    {
        $grammar = $this->createMock(GrammarInterface::class);
        $builder = new UpdateBuilder($grammar);
        $this->assertSame($grammar, $builder->getGrammar());
    }

    public function testGetters()
    {
        $builder = $this->getUpdateBuilder()
            ->table('t1')
            ->set('c1', 123)
            ->where('c2', '=', 456);
        $this->assertSame('UPDATE', $builder->getPrefix());
        $this->assertSame('t1', $builder->getTable());
        $this->assertEquals(['c1' => new Sql('?', [123])], $builder->getUpdate());
        $this->assertQueryIs('(c2 = ?)', [456], $builder->getWhere());
    }

    public function testPrefix()
    {
        $query = $this->getUpdateBuilder()
            ->prefix('UPDATE IGNORE')
            ->table('t1')
            ->set('c1', 'foo')
            ->set('c2', 'bar')
            ->build();
        $this->assertQueryIs(
            'UPDATE IGNORE t1 SET c1 = ?, c2 = ?',
            ['foo', 'bar'],
            $query
        );
    }

    public function testSet()
    {
        $query = $this->getUpdateBuilder()
            ->table('t1')
            ->set('c1', new Sql('c1 + ?', [1]))
            ->set('c2', 100)
            ->build();
        $this->assertQueryIs(
            'UPDATE t1 SET c1 = c1 + ?, c2 = ?',
            [1, 100],
            $query
        );

        $builder = $this->getSelectBuilder()->select('c1')->from('t2')->limit(1);
        $query = $this->getUpdateBuilder()
            ->table('t1')
            ->set('c1', $builder)
            ->set('c2', 100)
            ->build();
        $this->assertQueryIs(
            'UPDATE t1 SET c1 = (SELECT c1 FROM t2 LIMIT ?), c2 = ?',
            [1, 100],
            $query
        );
    }

    public function testSetAll()
    {
        $query = $this->getUpdateBuilder()
            ->table('t1')
            ->setAll(['c1' => new Sql('c1 + ?', [1]), 'c2' => 100])
            ->build();
        $this->assertQueryIs(
            'UPDATE t1 SET c1 = c1 + ?, c2 = ?',
            [1, 100],
            $query
        );

        $builder = $this->getSelectBuilder()->select('c1')->from('t2')->limit(1);
        $query = $this->getUpdateBuilder()
            ->table('t1')
            ->setAll(['c1' => $builder, 'c2' => 100])
            ->build();
        $this->assertQueryIs(
            'UPDATE t1 SET c1 = (SELECT c1 FROM t2 LIMIT ?), c2 = ?',
            [1, 100],
            $query
        );
    }

    public function testWhere()
    {
        $query = $this->getUpdateBuilder()
            ->table('t1')
            ->set('c1', 123)
            ->where('c2', '=', 456)
            ->where('c3', '=', 789)
            ->build();
        $this->assertQueryIs(
            'UPDATE t1 SET c1 = ? WHERE ((c2 = ?) AND (c3 = ?))',
            [123, 456, 789],
            $query
        );
    }

    public function testOrWhere()
    {
        $query = $this->getUpdateBuilder()
            ->table('t1')
            ->set('c1', 123)
            ->where('c2', '=', 456)
            ->orWhere('c3', '=', 789)
            ->build();
        $this->assertQueryIs(
            'UPDATE t1 SET c1 = ? WHERE ((c2 = ?) OR (c3 = ?))',
            [123, 456, 789],
            $query
        );
    }
}
