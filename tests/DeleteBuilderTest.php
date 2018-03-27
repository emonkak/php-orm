<?php

namespace Emonkak\Orm\Tests;

use Emonkak\Orm\DeleteBuilder;
use Emonkak\Orm\Grammar\GrammarInterface;

/**
 * @covers Emonkak\Orm\DeleteBuilder
 */
class DeleteBuilderTest extends \PHPUnit_Framework_TestCase
{
    use QueryBuilderTestTrait;

    public function testGetGrammar()
    {
        $grammar = $this->createMock(GrammarInterface::class);
        $builder = new DeleteBuilder($grammar);
        $this->assertSame($grammar, $builder->getGrammar());
    }

    public function testGetters()
    {
        $builder = $this->createDeleteBuilder()
            ->from('t1')
            ->where('c1', '=', 123);
        $this->assertSame('DELETE', $builder->getPrefix());
        $this->assertSame('t1', $builder->getFrom());
        $this->assertQueryIs('(c1 = ?)', [123], $builder->getWhere());
    }

    public function testPrefix()
    {
        $query = $this->createDeleteBuilder()
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
        $query = $this->createDeleteBuilder()
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
        $query = $this->createDeleteBuilder()
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

    public function testGroupWhere()
    {
        $query = $this->createDeleteBuilder()
            ->from('t1')
            ->where('c1', '=', 'foo')
            ->groupWhere(function($builder) {
                return $builder
                    ->where('c2', '=', 'bar')
                    ->orWhere('c3', '=', 'baz');
            })
            ->build();
        $this->assertQueryIs(
            'DELETE FROM t1 WHERE ((c1 = ?) AND ((c2 = ?) OR (c3 = ?)))',
            ['foo', 'bar', 'baz'],
            $query
        );

        $query = $this->createDeleteBuilder()
            ->from('t1')
            ->where('c1', '=', 'foo')
            ->groupWhere(function($builder) {
                return $builder;
            })
            ->build();
        $this->assertQueryIs(
            'DELETE FROM t1 WHERE (c1 = ?)',
            ['foo'],
            $query
        );
    }

    public function testOrGroupWhere()
    {
        $query = $this->createDeleteBuilder()
            ->from('t1')
            ->where('c1', '=', 'foo')
            ->orGroupWhere(function($builder) {
                return $builder
                    ->where('c2', '=', 'bar')
                    ->where('c3', '=', 'baz');
            })
            ->build();
        $this->assertQueryIs(
            'DELETE FROM t1 WHERE ((c1 = ?) OR ((c2 = ?) AND (c3 = ?)))',
            ['foo', 'bar', 'baz'],
            $query
        );

        $query = $this->createDeleteBuilder()
            ->from('t1')
            ->where('c1', '=', 'foo')
            ->orGroupWhere(function($builder) {
                return $builder;
            })
            ->build();
        $this->assertQueryIs(
            'DELETE FROM t1 WHERE (c1 = ?)',
            ['foo'],
            $query
        );
    }
}
