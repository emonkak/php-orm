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
}
